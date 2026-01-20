<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SmsLog;

/**
 * Sparrow SMS Service
 * 
 * Handles SMS sending via Sparrow SMS API (Nepal)
 * Website: https://sparrowsms.com
 */
class SparrowSmsService
{
    protected $token;
    protected $from;
    protected $baseUrl = 'https://api.sparrowsms.com/v2/sms/';

    public function __construct()
    {
        $settings = \App\Models\Setting::current();
        $this->token = $settings->sparrow_sms_token ?? env('SPARROW_SMS_TOKEN');
        $this->from = $settings->sparrow_sms_from ?? env('SPARROW_SMS_FROM', 'SMS');
    }

    /**
     * Send SMS to single recipient
     */
    public function sendSms(string $phone, string $message, $gymId = null, $userId = null): array
    {
        try {
            // Check if Sparrow SMS is configured
            if (empty($this->token)) {
                throw new \Exception('Sparrow SMS token is not configured. Please set it in Settings.');
            }

            // Clean phone number
            $phone = $this->cleanPhoneNumber($phone);

            // Trim and validate message
            $message = trim($message);
            if (empty($message)) {
                throw new \Exception('Message cannot be empty');
            }

            // Log the message being sent (for debugging)
            Log::info('Sending Sparrow SMS', [
                'to' => $phone,
                'from' => $this->from,
                'message_length' => strlen($message),
                'message_preview' => substr($message, 0, 50),
            ]);

            // Send SMS via Sparrow SMS API
            $response = Http::post($this->baseUrl, [
                'token' => $this->token,
                'from' => $this->from,
                'to' => $phone,
                'text' => $message,
            ]);

            $result = $response->json();

            // Calculate cost (approximate)
            $cost = $this->calculateCost();

            // Check if SMS was sent successfully
            if ($response->successful() && isset($result['response_code']) && $result['response_code'] == 200) {
                // Log SMS
                $smsLog = SmsLog::create([
                    'phone_number' => $phone,
                    'message' => $message,
                    'status' => 'sent',
                    'provider' => 'sparrow',
                    'provider_response' => $result,
                    'cost' => $cost,
                    'gym_id' => $gymId,
                    'sent_by' => $userId,
                    'sent_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'message_id' => $result['id'] ?? null,
                    'sms_log_id' => $smsLog->id,
                ];
            } else {
                $errorMessage = $result['response'] ?? $result['message'] ?? 'Failed to send SMS';
                
                // Log failed SMS
                $smsLog = SmsLog::create([
                    'phone_number' => $phone,
                    'message' => $message,
                    'status' => 'failed',
                    'provider' => 'sparrow',
                    'provider_response' => $result,
                    'cost' => 0,
                    'gym_id' => $gymId,
                    'sent_by' => $userId,
                    'sent_at' => now(),
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'sms_log_id' => $smsLog->id,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Sparrow SMS Error: ' . $e->getMessage(), [
                'exception' => $e,
                'phone' => $phone,
            ]);

            // Log failed SMS
            SmsLog::create([
                'phone_number' => $phone ?? 'unknown',
                'message' => $message ?? '',
                'status' => 'failed',
                'provider' => 'sparrow',
                'provider_response' => ['error' => $e->getMessage()],
                'cost' => 0,
                'gym_id' => $gymId,
                'sent_by' => $userId,
                'sent_at' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send bulk SMS to multiple recipients
     */
    public function sendBulkSms(array $phones, string $message, $gymId = null, $userId = null): array
    {
        // Check if Sparrow SMS is configured
        if (empty($this->token)) {
            throw new \Exception('Sparrow SMS token is not configured. Please set it in Settings.');
        }

        $results = [];
        $successCount = 0;
        $failedCount = 0;

        // Trim and validate message
        $messageBody = trim($message);
        if (empty($messageBody)) {
            throw new \Exception('Message cannot be empty');
        }

        foreach ($phones as $phone) {
            try {
                // Clean phone number
                $cleanPhone = $this->cleanPhoneNumber($phone);

                // Send SMS via Sparrow SMS API
                $response = Http::post($this->baseUrl, [
                    'token' => $this->token,
                    'from' => $this->from,
                    'to' => $cleanPhone,
                    'text' => $messageBody,
                ]);

                $result = $response->json();

                // Calculate cost
                $cost = $this->calculateCost();

                // Check if SMS was sent successfully
                if ($response->successful() && isset($result['response_code']) && $result['response_code'] == 200) {
                    // Log SMS
                    SmsLog::create([
                        'phone_number' => $cleanPhone,
                        'message' => $messageBody,
                        'status' => 'sent',
                        'provider' => 'sparrow',
                        'provider_response' => $result,
                        'cost' => $cost,
                        'gym_id' => $gymId,
                        'sent_by' => $userId,
                        'sent_at' => now(),
                    ]);

                    $successCount++;
                    $results[] = [
                        'phone' => $cleanPhone,
                        'success' => true,
                        'id' => $result['id'] ?? null,
                    ];
                } else {
                    $errorMessage = $result['response'] ?? $result['message'] ?? 'Failed to send SMS';
                    
                    Log::warning('Sparrow SMS Failed', [
                        'phone' => $cleanPhone,
                        'error' => $errorMessage,
                        'result' => $result,
                    ]);

                    // Log failed SMS
                    SmsLog::create([
                        'phone_number' => $cleanPhone,
                        'message' => $messageBody,
                        'status' => 'failed',
                        'provider' => 'sparrow',
                        'provider_response' => $result,
                        'cost' => 0,
                        'gym_id' => $gymId,
                        'sent_by' => $userId,
                        'sent_at' => now(),
                    ]);

                    $failedCount++;
                    $results[] = [
                        'phone' => $cleanPhone,
                        'success' => false,
                        'error' => $errorMessage,
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Sparrow Bulk SMS Error for ' . $phone . ': ' . $e->getMessage());

                // Log failed SMS
                SmsLog::create([
                    'phone_number' => $this->cleanPhoneNumber($phone),
                    'message' => $messageBody,
                    'status' => 'failed',
                    'provider' => 'sparrow',
                    'provider_response' => ['error' => $e->getMessage()],
                    'cost' => 0,
                    'gym_id' => $gymId,
                    'sent_by' => $userId,
                    'sent_at' => now(),
                ]);

                $failedCount++;
                $results[] = [
                    'phone' => $phone,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $failedCount === 0,
            'total' => count($phones),
            'sent' => $successCount,
            'failed' => $failedCount,
            'results' => $results,
        ];
    }

    /**
     * Clean phone number format for Sparrow SMS
     * Sparrow SMS requires format: 977XXXXXXXXX (without +)
     */
    protected function cleanPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, remove it
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }

        // Add country code if not present (assume Nepal 977)
        if (strlen($phone) == 9) {
            // Nepal 9-digit number
            $phone = '977' . $phone;
        } elseif (strlen($phone) == 10 && preg_match('/^(98|97)/', $phone)) {
            // Already has country code but in wrong format
            // Do nothing, already correct
        } elseif (!str_starts_with($phone, '977')) {
            // Add Nepal country code
            if (preg_match('/^(98|97)/', $phone)) {
                $phone = '977' . $phone;
            }
        }

        return $phone;
    }

    /**
     * Calculate SMS cost (approximate)
     * Sparrow SMS pricing: NPR 0.50-1.50 per SMS
     */
    protected function calculateCost(): float
    {
        // Using average pricing
        return 1.00; // NPR 1.00 per SMS (average)
    }

    /**
     * Get account balance (if API supports it)
     */
    public function getBalance(): array
    {
        try {
            if (empty($this->token)) {
                return [
                    'success' => false,
                    'message' => 'Sparrow SMS token not configured',
                ];
            }

            // Sparrow SMS balance API (if available)
            $response = Http::get('https://api.sparrowsms.com/v2/balance/', [
                'token' => $this->token,
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['response_code']) && $result['response_code'] == 200) {
                return [
                    'success' => true,
                    'balance' => $result['balance'] ?? 'N/A',
                    'currency' => 'NPR',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get balance',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

