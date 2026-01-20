<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use App\Models\SmsLog;

/**
 * Twilio SMS Service
 * 
 * Handles SMS sending via Twilio API
 * Supports global coverage including Nepal and India
 */
class TwilioSmsService
{
    protected $accountSid;
    protected $authToken;
    protected $fromNumber;
    protected $client;

    public function __construct()
    {
        // Check if Twilio SDK is installed
        if (!class_exists('Twilio\Rest\Client')) {
            throw new \Exception('Twilio SDK is not installed. Please run: composer install');
        }

        $settings = \App\Models\Setting::current();
        $this->accountSid = $settings->twilio_account_sid ?? env('TWILIO_ACCOUNT_SID');
        $this->authToken = $settings->twilio_auth_token ?? env('TWILIO_AUTH_TOKEN');
        $this->fromNumber = $settings->twilio_from_number ?? env('TWILIO_FROM_NUMBER');

        if (!empty($this->accountSid) && !empty($this->authToken)) {
            $this->client = new Client($this->accountSid, $this->authToken);
        }
    }

    /**
     * Send SMS to single recipient
     */
    public function sendSms(string $phone, string $message, $gymId = null, $userId = null): array
    {
        try {
            // Check if Twilio is configured
            if (empty($this->accountSid) || empty($this->authToken) || empty($this->fromNumber)) {
                throw new \Exception('Twilio credentials are not configured. Please set Account SID, Auth Token, and From Number in Settings.');
            }

            // Clean phone number
            $phone = $this->cleanPhoneNumber($phone);

            // Trim and validate message
            $message = trim($message);
            if (empty($message)) {
                throw new \Exception('Message cannot be empty');
            }

            // Log the message being sent (for debugging)
            Log::info('Sending Twilio SMS', [
                'to' => $phone,
                'from' => $this->fromNumber,
                'message_length' => strlen($message),
                'message_preview' => substr($message, 0, 50),
            ]);

            // Send SMS via Twilio
            $twilioMessage = $this->client->messages->create(
                $phone, // to
                [
                    'from' => $this->fromNumber,
                    'body' => $message
                ]
            );

            // Calculate cost (approximate)
            $cost = $this->calculateCost($phone);

            // Log SMS
            $smsLog = SmsLog::create([
                'phone_number' => $phone,
                'message' => $message,
                'status' => 'sent',
                'provider' => 'twilio',
                'provider_response' => [
                    'sid' => $twilioMessage->sid,
                    'status' => $twilioMessage->status,
                    'price' => $twilioMessage->price ?? null,
                    'price_unit' => $twilioMessage->priceUnit ?? null,
                ],
                'cost' => $cost,
                'gym_id' => $gymId,
                'sent_by' => $userId,
                'sent_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'message_id' => $twilioMessage->sid,
                'sms_log_id' => $smsLog->id,
            ];
        } catch (\Exception $e) {
            Log::error('Twilio SMS Error: ' . $e->getMessage(), [
                'exception' => $e,
                'phone' => $phone,
            ]);

            // Log failed SMS
            SmsLog::create([
                'phone_number' => $phone ?? 'unknown',
                'message' => $message ?? '',
                'status' => 'failed',
                'provider' => 'twilio',
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
        // Check if Twilio is configured
        if (empty($this->accountSid) || empty($this->authToken) || empty($this->fromNumber)) {
            throw new \Exception('Twilio credentials are not configured. Please set Account SID, Auth Token, and From Number in Settings.');
        }

        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($phones as $phone) {
            try {
                // Clean phone number
                $cleanPhone = $this->cleanPhoneNumber($phone);

                // Trim and validate message
                $messageBody = trim($message);
                if (empty($messageBody)) {
                    throw new \Exception('Message cannot be empty');
                }

                // Send SMS via Twilio
                $twilioMessage = $this->client->messages->create(
                    $cleanPhone,
                    [
                        'from' => $this->fromNumber,
                        'body' => $messageBody
                    ]
                );

                // Calculate cost
                $cost = $this->calculateCost($cleanPhone);

                // Log SMS
                SmsLog::create([
                    'phone_number' => $cleanPhone,
                    'message' => $messageBody,
                    'status' => 'sent',
                    'provider' => 'twilio',
                    'provider_response' => [
                        'sid' => $twilioMessage->sid,
                        'status' => $twilioMessage->status,
                        'price' => $twilioMessage->price,
                        'price_unit' => $twilioMessage->priceUnit,
                    ],
                    'cost' => $cost,
                    'gym_id' => $gymId,
                    'sent_by' => $userId,
                    'sent_at' => now(),
                ]);

                $successCount++;
                $results[] = [
                    'phone' => $cleanPhone,
                    'success' => true,
                    'sid' => $twilioMessage->sid,
                ];
            } catch (\Exception $e) {
                Log::error('Twilio Bulk SMS Error for ' . $phone . ': ' . $e->getMessage());

                // Log failed SMS
                SmsLog::create([
                    'phone_number' => $this->cleanPhoneNumber($phone),
                    'message' => $messageBody ?? $message,
                    'status' => 'failed',
                    'provider' => 'twilio',
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
     * Clean phone number format for Twilio
     * Twilio requires E.164 format: +[country code][number]
     */
    protected function cleanPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If starts with 0, remove it
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }

        // Add country code if not present
        if (!str_starts_with($phone, '+')) {
            // Check if it's Nepal number (starts with 98 or 97)
            if (preg_match('/^(98|97)/', $phone)) {
                $phone = '+977' . $phone;
            } elseif (preg_match('/^[6-9]\d{9}$/', $phone)) {
                // Indian 10-digit number
                $phone = '+91' . $phone;
            } else {
                // Assume Nepal if 9 digits
                if (strlen($phone) == 9) {
                    $phone = '+977' . $phone;
                }
            }
        }

        return $phone;
    }

    /**
     * Calculate SMS cost (approximate)
     * Twilio pricing varies by country
     */
    protected function calculateCost(string $phone): float
    {
        // Twilio pricing (approximate)
        // Nepal: ~$0.0075 per SMS
        // India: ~$0.0075 per SMS
        // US: ~$0.0075 per SMS
        
        if (str_starts_with($phone, '+977')) {
            return 0.0075; // $0.0075 per SMS (Nepal)
        } elseif (str_starts_with($phone, '+91')) {
            return 0.0075; // $0.0075 per SMS (India)
        } elseif (str_starts_with($phone, '+1')) {
            return 0.0075; // $0.0075 per SMS (US/Canada)
        }
        
        return 0.0075; // Default
    }

    /**
     * Get account balance
     */
    public function getBalance(): array
    {
        try {
            if (empty($this->accountSid) || empty($this->authToken)) {
                return [
                    'success' => false,
                    'message' => 'Twilio credentials not configured',
                ];
            }

            $account = $this->client->api->v2010->account->fetch();
            
            return [
                'success' => true,
                'balance' => $account->balance ?? 'N/A',
                'currency' => $account->currency ?? 'USD',
                'status' => $account->status ?? 'active',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

