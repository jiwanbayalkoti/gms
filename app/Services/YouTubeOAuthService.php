<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class YouTubeOAuthService
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const SCOPES = [
        'https://www.googleapis.com/auth/youtube.upload',
        'https://www.googleapis.com/auth/youtube.readonly',
    ];

    public function hasCredentials(Setting $settings): bool
    {
        return trim((string) ($settings->youtube_client_id ?? '')) !== ''
            && trim((string) ($settings->youtube_client_secret ?? '')) !== '';
    }

    public function redirectUri(): string
    {
        return route('settings.youtube-callback');
    }

    public function buildAuthUrl(Setting $settings, string $state): string
    {
        $query = http_build_query([
            'client_id' => $settings->youtube_client_id,
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', self::SCOPES),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return self::AUTH_URL . '?' . $query;
    }

    /**
     * @return array{access_token: string, refresh_token: ?string, expires_in: int}
     */
    public function exchangeCode(Setting $settings, string $code): array
    {
        $res = Http::timeout(30)->asForm()->post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => $settings->youtube_client_id,
            'client_secret' => $settings->youtube_client_secret,
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (!$res->successful() || empty($res->json('access_token'))) {
            throw new \RuntimeException($res->json('error_description') ?: ($res->json('error') ?: $res->body()));
        }

        return [
            'access_token' => (string) $res->json('access_token'),
            'refresh_token' => $res->json('refresh_token') ? (string) $res->json('refresh_token') : null,
            'expires_in' => (int) ($res->json('expires_in') ?: 3600),
        ];
    }

    public function refreshAccessToken(Setting $settings): ?string
    {
        $refresh = trim((string) ($settings->youtube_refresh_token ?? ''));
        if ($refresh === '' || !$this->hasCredentials($settings)) {
            return null;
        }

        $res = Http::timeout(30)->asForm()->post(self::TOKEN_URL, [
            'client_id' => $settings->youtube_client_id,
            'client_secret' => $settings->youtube_client_secret,
            'refresh_token' => $refresh,
            'grant_type' => 'refresh_token',
        ]);

        if (!$res->successful() || empty($res->json('access_token'))) {
            return null;
        }

        $settings->youtube_access_token = (string) $res->json('access_token');
        if ($res->json('refresh_token')) {
            $settings->youtube_refresh_token = (string) $res->json('refresh_token');
        }
        $settings->save();

        return $settings->youtube_access_token;
    }

    /**
     * Valid access token for API calls (refresh when expired if possible).
     */
    public function getValidAccessToken(Setting $settings): ?string
    {
        $token = trim((string) ($settings->youtube_access_token ?? ''));
        if ($token === '') {
            return $this->refreshAccessToken($settings);
        }

        $expiresAt = $settings->youtube_token_expires_at ?? null;
        if ($expiresAt && now()->greaterThan($expiresAt)) {
            return $this->refreshAccessToken($settings) ?: $token;
        }

        return $token;
    }

    public function fetchMyChannelId(string $accessToken): ?string
    {
        $res = Http::timeout(30)->withToken($accessToken)->get('https://www.googleapis.com/youtube/v3/channels', [
            'part' => 'id,snippet',
            'mine' => 'true',
        ]);

        if (!$res->successful()) {
            return null;
        }

        $items = $res->json('items', []);
        if (!is_array($items) || count($items) === 0) {
            return null;
        }

        return (string) ($items[0]['id'] ?? '');
    }

    public function testConnection(Setting $settings): array
    {
        $token = $this->getValidAccessToken($settings);
        if (!$token) {
            return ['success' => false, 'message' => 'YouTube is not connected. Use Connect with YouTube or paste a valid access token.'];
        }

        $channelId = $this->fetchMyChannelId($token);
        if (!$channelId) {
            return ['success' => false, 'message' => 'Token works but no YouTube channel found for this Google account.'];
        }

        return [
            'success' => true,
            'message' => 'YouTube connected. Channel ID: ' . $channelId,
            'channel_id' => $channelId,
        ];
    }
}
