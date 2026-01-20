<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SmsLog;
use App\Models\Setting;
use App\Services\TextLocalSmsService;
use App\Services\TwilioSmsService;
use App\Services\SparrowSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BulkSmsController extends BaseController
{
    protected $smsService;

    public function __construct()
    {
        $settings = Setting::current();
        $provider = $settings->sms_provider ?? 'twilio';

        // Initialize SMS service based on provider
        try {
            if ($provider === 'twilio') {
                // Check if Twilio SDK is installed
                if (!class_exists('Twilio\Rest\Client')) {
                    \Log::error('Twilio SDK not installed. Run: composer install');
                    $this->smsService = null;
                } else {
                    $this->smsService = new TwilioSmsService();
                }
            } elseif ($provider === 'sparrow') {
                $this->smsService = new SparrowSmsService();
            } else {
                $this->smsService = new TextLocalSmsService();
            }
        } catch (\Exception $e) {
            \Log::error('SMS Service initialization error: ' . $e->getMessage());
            $this->smsService = null;
        }
    }

    /**
     * Display bulk SMS sending page with statistics.
     */
    public function index()
    {
        $this->authorizePermission('notifications.create');

        $user = Auth::user();
        
        // Check if SMS provider is configured
        $settings = Setting::current();
        $provider = $settings->sms_provider ?? 'twilio';
        
        // Check if Twilio SDK is installed
        $twilioInstalled = class_exists('Twilio\Rest\Client');
        
        if ($provider === 'twilio') {
            $isConfigured = $twilioInstalled && 
                          !empty($settings->twilio_account_sid) && 
                          !empty($settings->twilio_auth_token) && 
                          !empty($settings->twilio_from_number);
        } elseif ($provider === 'sparrow') {
            $isConfigured = !empty($settings->sparrow_sms_token) && 
                          !empty($settings->sparrow_sms_from);
        } else {
            $isConfigured = !empty($settings->textlocal_api_key);
        }
        
        // Get SMS statistics
        $todayCount = SmsLog::today()
            ->when(!$user->isSuperAdmin() && $user->gym_id, function($q) use ($user) {
                return $q->where('gym_id', $user->gym_id);
            })
            ->where('status', 'sent')
            ->count();

        $todayCost = SmsLog::today()
            ->when(!$user->isSuperAdmin() && $user->gym_id, function($q) use ($user) {
                return $q->where('gym_id', $user->gym_id);
            })
            ->where('status', 'sent')
            ->sum('cost');

        $monthCount = SmsLog::whereMonth('sent_at', Carbon::now()->month)
            ->whereYear('sent_at', Carbon::now()->year)
            ->when(!$user->isSuperAdmin() && $user->gym_id, function($q) use ($user) {
                return $q->where('gym_id', $user->gym_id);
            })
            ->where('status', 'sent')
            ->count();

        $monthCost = SmsLog::whereMonth('sent_at', Carbon::now()->month)
            ->whereYear('sent_at', Carbon::now()->year)
            ->when(!$user->isSuperAdmin() && $user->gym_id, function($q) use ($user) {
                return $q->where('gym_id', $user->gym_id);
            })
            ->where('status', 'sent')
            ->sum('cost');

        // Get recent SMS logs
        $recentLogs = SmsLog::with(['sender', 'gym'])
            ->when(!$user->isSuperAdmin() && $user->gym_id, function($q) use ($user) {
                return $q->where('gym_id', $user->gym_id);
            })
            ->latest('sent_at')
            ->limit(50)
            ->get();

        // Get members for selection
        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        $twilioInstalled = class_exists('Twilio\Rest\Client');
        $provider = $settings->sms_provider ?? 'twilio';
        
        return view('bulk-sms.index', compact('todayCount', 'todayCost', 'monthCount', 'monthCost', 'recentLogs', 'members', 'isConfigured', 'settings', 'twilioInstalled', 'provider'));
    }

    /**
     * Send bulk SMS.
     */
    public function send(Request $request)
    {
        $this->authorizePermission('notifications.create');

        $validated = $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'send_to_all_members' => 'boolean',
        ]);

        $user = Auth::user();

        // Get recipients
        if ($request->has('send_to_all_members') && $request->send_to_all_members) {
            $recipientsQuery = User::where('role', 'Member')->whereNotNull('phone');
            if (!$user->isSuperAdmin() && $user->gym_id) {
                $recipientsQuery->where('gym_id', $user->gym_id);
            }
            $recipients = $recipientsQuery->get();
        } else {
            $recipients = User::whereIn('id', $validated['recipients'])
                ->whereNotNull('phone')
                ->get();
        }

        if ($recipients->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No recipients found with phone numbers.',
            ], 422);
        }

        // Get phone numbers
        $phones = $recipients->pluck('phone')->filter()->toArray();

        // Check if SMS service is available
        if (!$this->smsService) {
            $provider = Setting::current()->sms_provider ?? 'twilio';
            if ($provider === 'twilio') {
                $errorMsg = 'Twilio SDK is not installed. Please run: composer install';
            } elseif ($provider === 'sparrow') {
                $errorMsg = 'Sparrow SMS is not configured properly.';
            } else {
                $errorMsg = 'SMS service is not configured properly.';
            }
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                ], 500);
            }
            
            return redirect()->route('bulk-sms.index')
                ->with('error', $errorMsg);
        }

        try {
            // Trim and validate message
            $message = trim($validated['message']);
            if (empty($message)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Message cannot be empty.',
                    ], 422);
                }
                return redirect()->route('bulk-sms.index')
                    ->with('error', 'Message cannot be empty.');
            }

            // Log the message being sent (for debugging)
            \Log::info('Bulk SMS Request', [
                'recipients_count' => count($phones),
                'message_length' => strlen($message),
                'message_preview' => substr($message, 0, 50),
            ]);

            // Send bulk SMS
            $result = $this->smsService->sendBulkSms(
                $phones,
                $message,
                $user->gym_id,
                $user->id
            );

            // Get error message from first failed result if available
            $errorMessage = null;
            if (!$result['success'] && !empty($result['results'])) {
                foreach ($result['results'] as $apiResult) {
                    if (isset($apiResult['errors']) && is_array($apiResult['errors']) && count($apiResult['errors']) > 0) {
                        $errorMessage = $apiResult['errors'][0]['message'] ?? null;
                        break;
                    } elseif (isset($apiResult['message'])) {
                        $errorMessage = $apiResult['message'];
                        break;
                    }
                }
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['success'] 
                        ? "SMS sent to {$result['sent']} recipients successfully." 
                        : ($errorMessage ?: "Failed to send some SMS. Sent: {$result['sent']}, Failed: {$result['failed']}"),
                    'data' => $result,
                ]);
            }

            return redirect()->route('bulk-sms.index')
                ->with('success', $result['success'] 
                    ? "SMS sent to {$result['sent']} recipients successfully." 
                    : ($errorMessage ?: "Failed to send some SMS. Sent: {$result['sent']}, Failed: {$result['failed']}"));
        } catch (\Exception $e) {
            \Log::error('Bulk SMS Error: ' . $e->getMessage(), [
                'exception' => $e,
                'phones_count' => count($phones),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'SMS sending failed: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('bulk-sms.index')
                ->with('error', 'SMS sending failed: ' . $e->getMessage());
        }

        return redirect()->route('bulk-sms.index')
            ->with('success', "SMS sent to {$result['sent']} recipients successfully.");
    }

    /**
     * Get SMS statistics (AJAX).
     */
    public function statistics(Request $request)
    {
        $this->authorizePermission('notifications.view');

        $user = Auth::user();
        $date = $request->get('date', Carbon::today()->toDateString());

        $query = SmsLog::whereDate('sent_at', $date)
            ->when(!$user->isSuperAdmin() && $user->gym_id, function($q) use ($user) {
                return $q->where('gym_id', $user->gym_id);
            });

        $sent = (clone $query)->where('status', 'sent')->count();
        $failed = (clone $query)->where('status', 'failed')->count();
        $cost = (clone $query)->where('status', 'sent')->sum('cost');

        return response()->json([
            'success' => true,
            'date' => $date,
            'sent' => $sent,
            'failed' => $failed,
            'total' => $sent + $failed,
            'cost' => round($cost, 2),
        ]);
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $this->authorizePermission('notifications.create');

        $user = Auth::user();
        $settings = Setting::current();
        $provider = $settings->sms_provider ?? 'twilio';
        $twilioInstalled = class_exists('Twilio\Rest\Client');
        
        if ($provider === 'twilio') {
            $isConfigured = $twilioInstalled && 
                          !empty($settings->twilio_account_sid) && 
                          !empty($settings->twilio_auth_token) && 
                          !empty($settings->twilio_from_number);
        } elseif ($provider === 'sparrow') {
            $isConfigured = !empty($settings->sparrow_sms_token) && 
                          !empty($settings->sparrow_sms_from);
        } else {
            $isConfigured = !empty($settings->textlocal_api_key);
        }
        
        $todayCount = SmsLog::today()
            ->when(!$user->isSuperAdmin() && $user->gym_id, function($q) use ($user) {
                return $q->where('gym_id', $user->gym_id);
            })
            ->where('status', 'sent')
            ->count();

        $todayCost = SmsLog::today()
            ->when(!$user->isSuperAdmin() && $user->gym_id, function($q) use ($user) {
                return $q->where('gym_id', $user->gym_id);
            })
            ->where('status', 'sent')
            ->sum('cost');

        $monthCount = SmsLog::whereMonth('sent_at', Carbon::now()->month)
            ->whereYear('sent_at', Carbon::now()->year)
            ->when(!$user->isSuperAdmin() && $user->gym_id, function($q) use ($user) {
                return $q->where('gym_id', $user->gym_id);
            })
            ->where('status', 'sent')
            ->count();

        $monthCost = SmsLog::whereMonth('sent_at', Carbon::now()->month)
            ->whereYear('sent_at', Carbon::now()->year)
            ->when(!$user->isSuperAdmin() && $user->gym_id, function($q) use ($user) {
                return $q->where('gym_id', $user->gym_id);
            })
            ->where('status', 'sent')
            ->sum('cost');

        return $this->apiSuccess([
            'is_configured' => $isConfigured,
            'provider' => $provider,
            'statistics' => [
                'today' => [
                    'count' => $todayCount,
                    'cost' => round($todayCost, 2)
                ],
                'month' => [
                    'count' => $monthCount,
                    'cost' => round($monthCost, 2)
                ]
            ]
        ], 'Bulk SMS data retrieved successfully');
    }

    public function apiSend(Request $request)
    {
        $this->authorizePermission('notifications.create');

        if (!$this->smsService) {
            return $this->apiError('SMS service is not configured.', null, 500);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $recipients = User::whereIn('id', $validated['recipients'])->get();
        
        foreach ($recipients as $recipient) {
            if (!$user->isSuperAdmin() && $recipient->gym_id !== $user->gym_id) {
                return $this->apiError('Invalid recipient selected.', null, 422);
            }
        }

        try {
            $sent = 0;
            $failed = 0;
            $totalCost = 0;

            foreach ($recipients as $recipient) {
                if (!$recipient->phone) {
                    $failed++;
                    continue;
                }

                $result = $this->smsService->send($recipient->phone, $validated['message']);
                
                SmsLog::create([
                    'gym_id' => $user->gym_id,
                    'user_id' => $recipient->id,
                    'phone' => $recipient->phone,
                    'message' => $validated['message'],
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'cost' => $result['cost'] ?? 0,
                    'sent_at' => $result['success'] ? now() : null,
                    'error_message' => $result['error'] ?? null,
                ]);

                if ($result['success']) {
                    $sent++;
                    $totalCost += $result['cost'] ?? 0;
                } else {
                    $failed++;
                }
            }

            return $this->apiSuccess([
                'sent' => $sent,
                'failed' => $failed,
                'total_cost' => round($totalCost, 2)
            ], "SMS sent to {$sent} recipients successfully");
        } catch (\Exception $e) {
            return $this->apiError('SMS sending failed: ' . $e->getMessage(), null, 500);
        }
    }

    public function apiStatistics(Request $request)
    {
        $this->authorizePermission('notifications.view');

        $user = Auth::user();
        $date = $request->get('date', Carbon::today()->toDateString());

        $query = SmsLog::whereDate('sent_at', $date)
            ->when(!$user->isSuperAdmin() && $user->gym_id, function($q) use ($user) {
                return $q->where('gym_id', $user->gym_id);
            });

        $sent = (clone $query)->where('status', 'sent')->count();
        $failed = (clone $query)->where('status', 'failed')->count();
        $cost = (clone $query)->where('status', 'sent')->sum('cost');

        return $this->apiSuccess([
            'date' => $date,
            'sent' => $sent,
            'failed' => $failed,
            'total' => $sent + $failed,
            'cost' => round($cost, 2)
        ], 'SMS statistics retrieved successfully');
    }
}
