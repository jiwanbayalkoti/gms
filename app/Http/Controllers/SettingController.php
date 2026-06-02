<?php

namespace App\Http\Controllers;

use App\Models\Gym;
use App\Models\Setting;
use App\Services\YouTubeOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SettingController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:GymAdmin']);
    }
    
    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $settingsGymId = $this->resolveSettingsGymId($request);
        Setting::rememberGymContext($settingsGymId);

        $settings = Setting::current($settingsGymId);
        $settings->loadMissing('gym');
        $gyms = ($user && $user->isSuperAdmin())
            ? Gym::query()->orderBy('name')->get(['id', 'name'])
            : collect();

        $metaOAuthHints = $this->metaOAuthSetupHints($request);

        return view('settings.index', array_merge(compact(
            'settings',
            'gyms',
            'settingsGymId',
        ), $metaOAuthHints));
    }
    
    /**
     * Update the settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'gym_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'footer_text' => 'nullable|string|max:255',
            'enable_online_booking' => 'boolean',
            'enable_online_payments' => 'boolean',
            'enable_sms_notifications' => 'boolean',
            'sms_provider' => 'nullable|string|in:twilio,textlocal,sparrow',
            'textlocal_api_key' => 'nullable|string|max:255',
            'textlocal_sender_id' => 'nullable|string|max:20',
            'twilio_account_sid' => 'nullable|string|max:255',
            'twilio_auth_token' => 'nullable|string|max:255',
            'twilio_from_number' => 'nullable|string|max:20',
            'sparrow_sms_token' => 'nullable|string|max:255',
            'sparrow_sms_from' => 'nullable|string|max:11',
            'facebook_app_id' => 'nullable|string|max:255',
            'facebook_app_secret' => 'nullable|string',
            'youtube_client_id' => 'nullable|string|max:255',
            'youtube_client_secret' => 'nullable|string',
            'enable_email_notifications' => 'boolean',
            'enable_pause_feature' => 'boolean',
            'minimum_pause_days' => 'required|integer|min:1',
        ]);
        
        $settingsGymId = $this->resolveSettingsGymId($request);
        Setting::rememberGymContext($settingsGymId);
        $settings = Setting::current($settingsGymId);

        // Handle checkbox fields that may not be present in the request
        $settings->enable_online_booking = $request->has('enable_online_booking');
        $settings->enable_online_payments = $request->has('enable_online_payments');
        $settings->enable_sms_notifications = $request->has('enable_sms_notifications');
        $settings->sms_provider = $request->input('sms_provider', 'twilio');
        $settings->textlocal_api_key = $request->input('textlocal_api_key');
        $settings->textlocal_sender_id = $request->input('textlocal_sender_id');
        $settings->twilio_account_sid = $request->input('twilio_account_sid');
        $settings->twilio_auth_token = $request->input('twilio_auth_token');
        $settings->twilio_from_number = $request->input('twilio_from_number');
        $settings->sparrow_sms_token = $request->input('sparrow_sms_token');
        $settings->sparrow_sms_from = $request->input('sparrow_sms_from');
        $settings->facebook_app_id = $request->input('facebook_app_id');
        $settings->facebook_app_secret = $request->input('facebook_app_secret');
        $settings->youtube_client_id = $request->input('youtube_client_id');
        $settings->youtube_client_secret = $request->input('youtube_client_secret');
        // Meta/YouTube tokens and IDs are set only via OAuth (Connect buttons), not manual paste.
        $settings->enable_email_notifications = $request->has('enable_email_notifications');
        $settings->enable_pause_feature = $request->has('enable_pause_feature');
        $settings->minimum_pause_days = $request->input('minimum_pause_days', 7);
        
        // Handle file upload for logo
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo && Storage::exists('public/' . $settings->logo)) {
                Storage::delete('public/' . $settings->logo);
            }
            
            $logoPath = $request->file('logo')->store('logos', 'public');
            $settings->logo = $logoPath;
        }
        
        // Update other fields
        $settings->gym_name = $request->gym_name;
        $settings->email = $request->email;
        $settings->phone = $request->phone;
        $settings->address = $request->address;
        $settings->primary_color = $request->primary_color;
        $settings->secondary_color = $request->secondary_color;
        $settings->footer_text = $request->footer_text;
        
        $settings->save();
        
        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $settings = Setting::current();
        return $this->apiSuccess($settings, 'Settings retrieved successfully');
    }

    public function apiUpdate(Request $request)
    {
        $request->validate([
            'gym_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'footer_text' => 'nullable|string|max:255',
            'enable_online_booking' => 'boolean',
            'enable_online_payments' => 'boolean',
            'enable_sms_notifications' => 'boolean',
            'sms_provider' => 'nullable|string|in:twilio,textlocal,sparrow',
            'textlocal_api_key' => 'nullable|string|max:255',
            'textlocal_sender_id' => 'nullable|string|max:20',
            'twilio_account_sid' => 'nullable|string|max:255',
            'twilio_auth_token' => 'nullable|string|max:255',
            'twilio_from_number' => 'nullable|string|max:20',
            'sparrow_sms_token' => 'nullable|string|max:255',
            'sparrow_sms_from' => 'nullable|string|max:11',
            'facebook_app_id' => 'nullable|string|max:255',
            'facebook_app_secret' => 'nullable|string',
            'youtube_client_id' => 'nullable|string|max:255',
            'youtube_client_secret' => 'nullable|string',
            'enable_email_notifications' => 'boolean',
            'enable_pause_feature' => 'boolean',
            'minimum_pause_days' => 'required|integer|min:1',
        ]);
        
        $settingsGymId = $this->resolveSettingsGymId($request);
        Setting::rememberGymContext($settingsGymId);
        $settings = Setting::current($settingsGymId);

        $settings->enable_online_booking = $request->has('enable_online_booking');
        $settings->enable_online_payments = $request->has('enable_online_payments');
        $settings->enable_sms_notifications = $request->has('enable_sms_notifications');
        $settings->sms_provider = $request->input('sms_provider', 'twilio');
        $settings->textlocal_api_key = $request->input('textlocal_api_key');
        $settings->textlocal_sender_id = $request->input('textlocal_sender_id');
        $settings->twilio_account_sid = $request->input('twilio_account_sid');
        $settings->twilio_auth_token = $request->input('twilio_auth_token');
        $settings->twilio_from_number = $request->input('twilio_from_number');
        $settings->sparrow_sms_token = $request->input('sparrow_sms_token');
        $settings->sparrow_sms_from = $request->input('sparrow_sms_from');
        $settings->facebook_app_id = $request->input('facebook_app_id');
        $settings->facebook_app_secret = $request->input('facebook_app_secret');
        $settings->youtube_client_id = $request->input('youtube_client_id');
        $settings->youtube_client_secret = $request->input('youtube_client_secret');
        // Meta/YouTube tokens and IDs are set only via OAuth (Connect buttons), not manual paste.
        $settings->enable_email_notifications = $request->has('enable_email_notifications');
        $settings->enable_pause_feature = $request->has('enable_pause_feature');
        $settings->minimum_pause_days = $request->input('minimum_pause_days', 7);
        
        if ($request->hasFile('logo')) {
            if ($settings->logo && Storage::exists('public/' . $settings->logo)) {
                Storage::delete('public/' . $settings->logo);
            }
            $logoPath = $request->file('logo')->store('logos', 'public');
            $settings->logo = $logoPath;
        }
        
        $settings->gym_name = $request->gym_name;
        $settings->email = $request->email;
        $settings->phone = $request->phone;
        $settings->address = $request->address;
        $settings->primary_color = $request->primary_color;
        $settings->secondary_color = $request->secondary_color;
        $settings->footer_text = $request->footer_text;
        
        $settings->save();
        
        return $this->apiSuccess($settings, 'Settings updated successfully');
    }

    /**
     * Test Facebook Page credentials from settings.
     */
    public function testFacebookConnection(Request $request)
    {
        $settings = Setting::current();
        $pageId = $request->input('facebook_page_id', $settings->facebook_page_id);
        $token = $request->input('facebook_page_access_token', $settings->facebook_page_access_token);

        if (empty($pageId) || empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Facebook Page ID and Access Token are required.',
            ], 422);
        }

        $response = Http::get("https://graph.facebook.com/v20.0/{$pageId}", [
            'fields' => 'id,name',
            'access_token' => $token,
        ]);

        if ($response->successful() && $response->json('id')) {
            return response()->json([
                'success' => true,
                'message' => 'Facebook connection successful.',
                'data' => $response->json(),
            ]);
        }

        $json = $response->json();
        $errorMessage = $this->formatMetaApiErrorMessage($json, $response->body());

        return response()->json([
            'success' => false,
            'message' => $errorMessage,
            'token_expired' => $this->isMetaAccessTokenExpired($json),
            'raw' => $json,
        ], 400);
    }

    /**
     * Inspect Page/User token via Meta debug_token (shows real granted scopes).
     */
    public function debugMetaPageToken(Request $request)
    {
        $settings = Setting::current();
        $appId = trim((string) ($settings->facebook_app_id ?? ''));
        $appSecret = trim((string) ($settings->facebook_app_secret ?? ''));
        $inputToken = trim((string) $request->input('input_token', $settings->facebook_page_access_token ?? ''));

        if ($appId === '' || $appSecret === '') {
            return response()->json([
                'success' => false,
                'message' => 'Save Meta App ID and Meta App Secret first.',
            ], 422);
        }

        if ($inputToken === '') {
            return response()->json([
                'success' => false,
                'message' => 'No token to inspect. Use Connect with Meta first.',
            ], 422);
        }

        $appAccessToken = $appId . '|' . $appSecret;
        $res = Http::timeout(30)->get('https://graph.facebook.com/debug_token', [
            'input_token' => $inputToken,
            'access_token' => $appAccessToken,
        ]);

        $json = $res->json();
        if (!$res->successful() || empty($json['data'])) {
            return response()->json([
                'success' => false,
                'message' => $json['error']['message'] ?? $res->body(),
                'raw' => $json,
            ], 400);
        }

        $data = $json['data'];
        $scopes = $data['scopes'] ?? $data['granular_scopes'] ?? [];
        $hasPublishActions = false;
        if (is_array($scopes)) {
            foreach ($scopes as $s) {
                if (is_string($s) && stripos($s, 'publish_actions') !== false) {
                    $hasPublishActions = true;
                    break;
                }
                if (is_array($s) && isset($s['scope']) && stripos((string) $s['scope'], 'publish_actions') !== false) {
                    $hasPublishActions = true;
                    break;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => $hasPublishActions
                ? 'This token still includes deprecated publish_actions. Remove the app from Facebook Settings → Business Integrations, then use Connect with Meta again (or generate a fresh Page token without that scope).'
                : 'Token looks OK for modern Page posting (no publish_actions in reported scopes). If publish still fails, check Page role and app mode (Live vs Development).',
            'data' => $data,
        ]);
    }

    public function redirectToMeta(Request $request)
    {
        $this->storeOAuthGymContext($request);
        $settings = $this->settingsForOAuth($request);
        $appId = (string) ($settings->facebook_app_id ?? '');
        $appSecret = (string) ($settings->facebook_app_secret ?? '');

        if ($appId === '' || $appSecret === '') {
            return redirect()->route('settings.index')
                ->with('error', 'Please save Meta App ID and Meta App Secret in settings first.');
        }

        $state = bin2hex(random_bytes(20));
        $request->session()->put('meta_oauth_state', $state);

        $redirectUri = $this->metaOAuthRedirectUri($request);
        $request->session()->put('meta_oauth_redirect_uri', $redirectUri);

        $query = http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'response_type' => 'code',
            'auth_type' => 'rerequest',
            'scope' => implode(',', [
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_posts',
                'instagram_basic',
                'instagram_content_publish',
            ]),
        ]);

        return redirect()->away('https://www.facebook.com/v20.0/dialog/oauth?' . $query);
    }

    public function handleMetaCallback(Request $request)
    {
        $sessionState = (string) $request->session()->pull('meta_oauth_state', '');
        $state = (string) $request->query('state', '');
        if ($sessionState === '' || !hash_equals($sessionState, $state)) {
            return redirect()->route('settings.index')->with('error', 'Meta OAuth state mismatch. Please try again.');
        }

        if ($request->filled('error')) {
            return redirect()->route('settings.index')
                ->with('error', 'Meta authorization failed: ' . ($request->query('error_description') ?: $request->query('error')));
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            return redirect()->route('settings.index')->with('error', 'Meta did not return an authorization code.');
        }

        $settings = $this->settingsForOAuth($request);
        $appId = (string) ($settings->facebook_app_id ?? '');
        $appSecret = (string) ($settings->facebook_app_secret ?? '');
        if ($appId === '' || $appSecret === '') {
            return redirect()->route('settings.index')->with('error', 'Meta App credentials are missing in settings.');
        }

        $redirectUri = (string) $request->session()->pull('meta_oauth_redirect_uri', '');
        if ($redirectUri === '') {
            $redirectUri = $this->metaOAuthRedirectUri($request);
        }

        $tokenRes = Http::timeout(30)->get('https://graph.facebook.com/v20.0/oauth/access_token', [
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if (!$tokenRes->successful() || empty($tokenRes->json('access_token'))) {
            return redirect()->route('settings.index')->with('error', 'Failed to obtain Meta user token: ' . ($tokenRes->json('error.message') ?: $tokenRes->body()));
        }

        $shortUserToken = (string) $tokenRes->json('access_token');
        $longTokenRes = Http::timeout(30)->get('https://graph.facebook.com/v20.0/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'fb_exchange_token' => $shortUserToken,
        ]);
        $userAccessToken = (string) ($longTokenRes->json('access_token') ?: $shortUserToken);

        $pagesRes = Http::timeout(30)->get('https://graph.facebook.com/v20.0/me/accounts', [
            'fields' => 'id,name,access_token,tasks',
            'access_token' => $userAccessToken,
        ]);
        $pages = $pagesRes->json('data', []);
        if (!$pagesRes->successful() || !is_array($pages) || count($pages) === 0) {
            return redirect()->route('settings.index')->with('error', 'No Facebook pages found for this Meta user/app.');
        }

        $selectedPage = collect($pages)->first(function ($page) {
            $tasks = collect($page['tasks'] ?? []);
            return $tasks->contains('CREATE_CONTENT') || $tasks->contains('MANAGE');
        }) ?? $pages[0];

        $pageId = (string) ($selectedPage['id'] ?? '');
        $pageAccessToken = (string) ($selectedPage['access_token'] ?? '');
        if ($pageId === '' || $pageAccessToken === '') {
            return redirect()->route('settings.index')->with('error', 'Unable to resolve page access token from Meta.');
        }

        $igRes = Http::timeout(30)->get("https://graph.facebook.com/v20.0/{$pageId}", [
            'fields' => 'instagram_business_account{id,username}',
            'access_token' => $pageAccessToken,
        ]);
        $igId = (string) ($igRes->json('instagram_business_account.id') ?? '');

        $settings->facebook_access_token = $userAccessToken;
        $settings->facebook_page_id = $pageId;
        $settings->facebook_page_access_token = $pageAccessToken;
        if ($igId !== '') {
            $settings->instagram_business_account_id = $igId;
        }
        $settings->save();

        $pageName = (string) ($selectedPage['name'] ?? $pageId);
        $msg = "Meta connected successfully. Page: {$pageName}";
        if ($igId !== '') {
            $msg .= ' · Instagram Business ID fetched.';
        } else {
            $msg .= ' · Instagram Business account not linked to this page.';
        }

        return redirect()->route('settings.index')->with('success', $msg);
    }

    public function redirectToYoutube(Request $request, YouTubeOAuthService $youtube)
    {
        $this->storeOAuthGymContext($request);
        $settings = $this->settingsForOAuth($request);
        if (!$youtube->hasCredentials($settings)) {
            return redirect()->route('settings.index')
                ->with('error', 'Save YouTube Client ID and Client Secret first, then click Connect with YouTube.');
        }

        $state = bin2hex(random_bytes(20));
        $request->session()->put('youtube_oauth_state', $state);

        return redirect()->away($youtube->buildAuthUrl($settings, $state));
    }

    public function handleYoutubeCallback(Request $request, YouTubeOAuthService $youtube)
    {
        $sessionState = (string) $request->session()->pull('youtube_oauth_state', '');
        $state = (string) $request->query('state', '');
        if ($sessionState === '' || !hash_equals($sessionState, $state)) {
            return redirect()->route('settings.index')->with('error', 'YouTube OAuth state mismatch. Please try again.');
        }

        if ($request->filled('error')) {
            return redirect()->route('settings.index')
                ->with('error', 'YouTube authorization failed: ' . ($request->query('error_description') ?: $request->query('error')));
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            return redirect()->route('settings.index')->with('error', 'YouTube did not return an authorization code.');
        }

        $settings = $this->settingsForOAuth($request);
        if (!$youtube->hasCredentials($settings)) {
            return redirect()->route('settings.index')->with('error', 'YouTube API credentials are missing in settings.');
        }

        try {
            $tokens = $youtube->exchangeCode($settings, $code);
        } catch (\Throwable $e) {
            return redirect()->route('settings.index')->with('error', 'YouTube token exchange failed: ' . $e->getMessage());
        }

        $settings->youtube_access_token = $tokens['access_token'];
        if (!empty($tokens['refresh_token'])) {
            $settings->youtube_refresh_token = $tokens['refresh_token'];
        }
        $settings->youtube_token_expires_at = now()->addSeconds(max(60, $tokens['expires_in'] - 60));

        $channelId = $youtube->fetchMyChannelId($tokens['access_token']);
        if ($channelId) {
            $settings->youtube_channel_id = $channelId;
        }

        $settings->save();

        $msg = 'YouTube connected successfully.';
        if ($channelId) {
            $msg .= ' Channel ID: ' . $channelId;
        } else {
            $msg .= ' Could not auto-detect channel ID — you may enter it manually.';
        }

        return redirect()->route('settings.index')->with('success', $msg);
    }

    public function testYoutubeConnection(Request $request, YouTubeOAuthService $youtube)
    {
        $settings = Setting::current();
        $result = $youtube->testConnection($settings);

        if ($result['success'] && !empty($result['channel_id']) && empty($settings->youtube_channel_id)) {
            $settings->youtube_channel_id = $result['channel_id'];
            $settings->save();
        }

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    private function resolveSettingsGymId(Request $request): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        if ($user->isSuperAdmin()) {
            $gymId = (int) $request->input('gym_id', 0);
            if ($gymId <= 0) {
                $gymId = (int) session('settings_gym_id', 0);
            }
            if ($gymId <= 0) {
                $gymId = (int) (Gym::query()->orderBy('name')->value('id') ?? 0);
            }

            return $gymId > 0 ? $gymId : null;
        }

        return $user->gym_id;
    }

    private function storeOAuthGymContext(Request $request): void
    {
        $gymId = $this->resolveSettingsGymId($request);
        if ($gymId !== null && $gymId > 0) {
            $request->session()->put('oauth_settings_gym_id', $gymId);
            Setting::rememberGymContext($gymId);
        }
    }

    private function settingsForOAuth(Request $request): Setting
    {
        $gymId = (int) $request->session()->get('oauth_settings_gym_id', 0);
        if ($gymId <= 0) {
            $gymId = (int) (Setting::resolveGymId() ?? 0);
        }

        return $gymId > 0 ? Setting::forGym($gymId) : Setting::current();
    }

    /**
     * @return array{
     *     metaOAuthRedirectUri: string,
     *     metaAppDomains: list<string>,
     *     metaOAuthRedirectUris: list<string>,
     *     metaUrlHostMismatch: bool,
     *     metaBrowserOrigin: string
     * }
     */
    private function metaOAuthSetupHints(Request $request): array
    {
        $activeRedirectUri = $this->metaOAuthRedirectUri($request);
        $callbackPath = route('settings.meta-callback', [], false);
        $browserOrigin = rtrim($request->getSchemeAndHttpHost(), '/');

        $redirectUris = collect([
            $activeRedirectUri,
            $browserOrigin . $callbackPath,
        ]);

        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl !== '') {
            $redirectUris->push($appUrl . $callbackPath);
        }

        if (trim((string) config('services.meta.redirect_uri', '')) === '') {
            foreach (['http://127.0.0.1:8000', 'http://localhost:8000', 'http://gym.test'] as $base) {
                $redirectUris->push(rtrim($base, '/') . $callbackPath);
            }
        }

        $redirectUris = $redirectUris->unique()->filter()->values()->all();

        $appDomains = collect([
            $request->getHost(),
            parse_url($appUrl, PHP_URL_HOST),
            parse_url($activeRedirectUri, PHP_URL_HOST),
        ])->filter(fn ($h) => is_string($h) && $h !== '')
            ->map(fn ($h) => strtolower(preg_replace('/:\d+$/', '', $h)))
            ->reject(fn ($h) => in_array($h, ['127.0.0.1', 'localhost'], true))
            ->unique()
            ->values()
            ->all();

        $appUrlHost = parse_url($appUrl, PHP_URL_HOST);
        $metaUrlHostMismatch = $appUrlHost && $request->getHost()
            && strtolower($appUrlHost) !== strtolower($request->getHost());

        return [
            'metaOAuthRedirectUri' => $activeRedirectUri,
            'metaAppDomain' => parse_url($activeRedirectUri, PHP_URL_HOST) ?: 'localhost',
            'metaAppDomains' => $appDomains,
            'metaOAuthRedirectUris' => $redirectUris,
            'metaUrlHostMismatch' => $metaUrlHostMismatch,
            'metaBrowserOrigin' => $browserOrigin,
        ];
    }

    /**
     * Must match Meta Developer Console → Facebook Login → Valid OAuth Redirect URIs exactly.
     */
    private function metaOAuthRedirectUri(?Request $request = null): string
    {
        $configured = trim((string) config('services.meta.redirect_uri', ''));
        if ($configured !== '') {
            return $configured;
        }

        $request = $request ?? request();
        if ($request) {
            $path = route('settings.meta-callback', [], false);

            return rtrim($request->getSchemeAndHttpHost(), '/') . $path;
        }

        return route('settings.meta-callback');
    }

    private function isMetaAccessTokenExpired(?array $json): bool
    {
        if (!is_array($json) || empty($json['error'])) {
            return false;
        }

        $error = $json['error'];
        $code = (int) ($error['code'] ?? 0);
        $subcode = (int) ($error['error_subcode'] ?? 0);
        if ($code === 190 && in_array($subcode, [463, 460, 467], true)) {
            return true;
        }

        $message = (string) ($error['message'] ?? '');

        return stripos($message, 'session has expired') !== false
            || stripos($message, 'error validating access token') !== false;
    }

    private function formatMetaApiErrorMessage(?array $json, string $fallback): string
    {
        if ($this->isMetaAccessTokenExpired($json)) {
            return 'Meta/Facebook token has expired. Click Connect with Meta below and sign in again.';
        }

        if (is_array($json) && !empty($json['error']['message'])) {
            return (string) $json['error']['message'];
        }

        return $fallback;
    }
}
