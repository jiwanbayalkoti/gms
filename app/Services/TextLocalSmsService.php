<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SmsLog;

/**
 * TextLocal SMS Service
 * 
 * Handles SMS sending via TextLocal API
 * Supports both India and Nepal
 */
class TextLocalSmsService
{
    protected $apiKey;
    protected $senderId;
    protected $baseUrl = 'https://api.textlocal.in/send/';

    public function __construct()
    {
        $settings = \App\Models\Setting::current();
        $this->apiKey = $settings->textlocal_api_key ?? env('TEXTLOCAL_API_KEY');
        $this->senderId = $settings->textlocal_sender_id ?? env('TEXTLOCAL_SENDER_ID', 'TXTLCL');
    }

    /**
     * Send SMS to single recipient
     */
    public function sendSms(string $phone, string $message, $gymId = null, $userId = null): array
    {
        try {
            // Clean phone number (remove spaces, dashes, etc.)
            $phone = $this->cleanPhoneNumber($phone);

            $response = Http::post($this->baseUrl, [
                'apikey' => $this->apiKey,
                'numbers' => $phone,
                'message' => $message,
                'sender' => $this->senderId,
            ]);

            $result = $response->json();

            // Check if API key is set
            if (empty($this->apiKey)) {
                throw new \Exception('TextLocal API Key is not configured. Please set it in Settings.');
            }

            // Log the response for debugging if failed
            if (!$response->successful() || !isset($result['status'])) {
                Log::error('TextLocal API Error', [
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                    'result' => $result,
                ]);
            }

            // Log SMS
            $smsLog = SmsLog::create([
                'phone_number' => $phone,
                'message' => $message,
                'status' => (isset($result['status']) && $result['status'] === 'success') ? 'sent' : 'failed',
                'provider' => 'textlocal',
                'provider_response' => $result,
                'cost' => $this->calculateCost($phone),
                'gym_id' => $gymId,
                'sent_by' => $userId,
                'sent_at' => now(),
            ]);

            if (isset($result['status']) && $result['status'] === 'success') {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'message_id' => $result['messages'][0]['id'] ?? null,
                    'sms_log_id' => $smsLog->id,
                ];
            } else {
                // Get error message
                $errorMessage = 'Failed to send SMS';
                if (isset($result['errors']) && is_array($result['errors']) && count($result['errors']) > 0) {
                    $errorMessage = $result['errors'][0]['message'] ?? $errorMessage;
                } elseif (isset($result['message'])) {
                    $errorMessage = $result['message'];
                } elseif (isset($result['warnings']) && is_array($result['warnings']) && count($result['warnings']) > 0) {
                    $errorMessage = $result['warnings'][0]['message'] ?? $errorMessage;
                }

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'sms_log_id' => $smsLog->id,
                    'error_details' => $result,
                ];
            }
        } catch (\Exception $e) {
            Log::error('TextLocal SMS Error: ' . $e->getMessage());

            // Log failed SMS
            SmsLog::create([
                'phone_number' => $phone,
                'message' => $message,
                'status' => 'failed',
                'provider' => 'textlocal',
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
        // Check if API key is set
        if (empty($this->apiKey)) {
            throw new \Exception('TextLocal API Key is not configured. Please set it in Settings.');
        }

        $results = [];
        $successCount = 0;
        $failedCount = 0;

        // TextLocal allows up to 1000 numbers per request
        $chunks = array_chunk($phones, 1000);

        foreach ($chunks as $chunk) {
            try {
                // Clean phone numbers
                $cleanPhones = array_map([$this, 'cleanPhoneNumber'], $chunk);
                $phoneString = implode(',', $cleanPhones);

                $response = Http::post($this->baseUrl, [
                    'apikey' => $this->apiKey,
                    'numbers' => $phoneString,
                    'message' => $message,
                    'sender' => $this->senderId,
                ]);

                $result = $response->json();

                // Log the response for debugging
                if (!$response->successful() || !isset($result['status'])) {
                    Log::error('TextLocal API Error', [
                        'status_code' => $response->status(),
                        'response' => $response->body(),
                        'result' => $result,
                        'api_key_set' => !empty($this->apiKey),
                    ]);
                }

                if (isset($result['status']) && $result['status'] === 'success') {
                    // Log each SMS
                    foreach ($cleanPhones as $index => $phone) {
                        SmsLog::create([
                            'phone_number' => $phone,
                            'message' => $message,
                            'status' => 'sent',
                            'provider' => 'textlocal',
                            'provider_response' => $result,
                            'cost' => $this->calculateCost($phone),
                            'gym_id' => $gymId,
                            'sent_by' => $userId,
                            'sent_at' => now(),
                        ]);
                        $successCount++;
                    }
                } else {
                    // Get error message
                    $errorMessage = 'Failed to send SMS';
                    if (isset($result['errors']) && is_array($result['errors']) && count($result['errors']) > 0) {
                        $errorMessage = $result['errors'][0]['message'] ?? $errorMessage;
                    } elseif (isset($result['message'])) {
                        $errorMessage = $result['message'];
                    }

                    Log::warning('TextLocal Bulk SMS Failed', [
                        'error' => $errorMessage,
                        'result' => $result,
                        'phones_count' => count($cleanPhones),
                    ]);

                    // Log failed SMS
                    foreach ($cleanPhones as $phone) {
                        SmsLog::create([
                            'phone_number' => $phone,
                            'message' => $message,
                            'status' => 'failed',
                            'provider' => 'textlocal',
                            'provider_response' => $result,
                            'cost' => 0,
                            'gym_id' => $gymId,
                            'sent_by' => $userId,
                            'sent_at' => now(),
                        ]);
                        $failedCount++;
                    }
                }

                $results[] = $result;
            } catch (\Exception $e) {
                Log::error('TextLocal Bulk SMS Error: ' . $e->getMessage());

                // Log all as failed
                foreach ($chunk as $phone) {
                    SmsLog::create([
                        'phone_number' => $this->cleanPhoneNumber($phone),
                        'message' => $message,
                        'status' => 'failed',
                        'provider' => 'textlocal',
                        'provider_response' => ['error' => $e->getMessage()],
                        'cost' => 0,
                        'gym_id' => $gymId,
                        'sent_by' => $userId,
                        'sent_at' => now(),
                    ]);
                    $failedCount++;
                }
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
     * Clean phone number format for TextLocal
     * TextLocal requires numbers without + sign, just country code + number
     */
    protected function cleanPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, remove it
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }

        // Add country code if not present (assume Nepal 977 or India 91)
        if (strlen($phone) == 10) {
            // Check if it's Nepal number (starts with 98 or 97)
            if (preg_match('/^(98|97)/', $phone)) {
                $phone = '977' . $phone;
            } elseif (preg_match('/^[6-9]\d{9}$/', $phone)) {
                // Indian 10-digit number
                $phone = '91' . $phone;
            }
        } elseif (strlen($phone) == 9) {
            // Nepal 9-digit number
            $phone = '977' . $phone;
        }

        return $phone;
    }

    /**
     * Calculate SMS cost (approximate)
     */
    protected function calculateCost(string $phone): float
    {
        // TextLocal pricing (approximate)
        // India: ₹0.20-0.30 per SMS
        // Nepal: NPR 1-2 per SMS
        // For now, using average
        if (str_starts_with($phone, '+91')) {
            return 0.25; // ₹0.25 per SMS (India)
        } elseif (str_starts_with($phone, '+977')) {
            return 1.50; // NPR 1.50 per SMS (Nepal)
        }
        return 0.30; // Default
    }

    /**
     * Get account balance (if API supports it)
     */
    public function getBalance(): array
    {
        try {
            $response = Http::post('https://api.textlocal.in/balance/', [
                'apikey' => $this->apiKey,
            ]);

            $result = $response->json();

            if ($result['status'] === 'success') {
                return [
                    'success' => true,
                    'balance' => $result['balance'] ?? 0,
                    'currency' => $result['currency'] ?? 'INR',
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

