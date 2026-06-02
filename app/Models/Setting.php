<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'gym_id',
        'gym_name',
        'email',
        'phone',
        'address',
        'logo',
        'primary_color',
        'secondary_color',
        'footer_text',
        'enable_online_booking',
        'enable_online_payments',
        'enable_sms_notifications',
        'sms_provider',
        'textlocal_api_key',
        'textlocal_sender_id',
        'twilio_account_sid',
        'twilio_auth_token',
        'twilio_from_number',
        'sparrow_sms_token',
        'sparrow_sms_from',
        'facebook_app_id',
        'facebook_app_secret',
        'facebook_access_token',
        'facebook_page_access_token',
        'facebook_page_id',
        'instagram_business_account_id',
        'youtube_client_id',
        'youtube_client_secret',
        'youtube_access_token',
        'youtube_refresh_token',
        'youtube_channel_id',
        'youtube_token_expires_at',
        'enable_email_notifications',
        'enable_pause_feature',
        'minimum_pause_days',
    ];

    protected $casts = [
        'enable_online_booking' => 'boolean',
        'enable_online_payments' => 'boolean',
        'enable_sms_notifications' => 'boolean',
        'enable_email_notifications' => 'boolean',
        'enable_pause_feature' => 'boolean',
        'minimum_pause_days' => 'integer',
        'youtube_token_expires_at' => 'datetime',
    ];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Gym context for settings / OAuth (session, query, or logged-in user).
     */
    public static function resolveGymId(): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        if ($user->isSuperAdmin()) {
            $fromRequest = (int) request()->input('gym_id', 0);
            $fromSession = (int) session('settings_gym_id', 0);
            $gymId = $fromRequest > 0 ? $fromRequest : $fromSession;

            return $gymId > 0 ? $gymId : null;
        }

        return $user->gym_id;
    }

    public static function rememberGymContext(?int $gymId): void
    {
        if ($gymId !== null && $gymId > 0) {
            session(['settings_gym_id' => $gymId]);
        }
    }

    /**
     * Settings for the active gym (logged-in user or SuperAdmin selection).
     */
    public static function current(?int $gymId = null): self
    {
        $gymId = $gymId ?? static::resolveGymId();

        if ($gymId !== null && $gymId > 0) {
            return static::forGym($gymId);
        }

        $settings = static::query()->orderBy('id')->first();

        return $settings ?? static::createDefault(null);
    }

    /**
     * Settings row for a specific gym (used when publishing social posts).
     */
    public static function forGym(int $gymId): self
    {
        $settings = static::where('gym_id', $gymId)->first();
        if ($settings) {
            return $settings;
        }

        $template = static::query()->whereNull('gym_id')->first()
            ?? static::query()->where('gym_id', '!=', $gymId)->first();

        if ($template) {
            if ($template->gym_id === null) {
                $template->gym_id = $gymId;
                $template->save();

                return $template;
            }

            $copy = $template->replicate();
            $copy->gym_id = $gymId;
            $copy->save();

            return $copy;
        }

        return static::createDefault($gymId);
    }

    protected static function createDefault(?int $gymId): self
    {
        $gymName = 'My Gym';
        if ($gymId !== null) {
            $gym = Gym::find($gymId);
            if ($gym && $gym->name) {
                $gymName = $gym->name;
            }
        }

        return static::create([
            'gym_id' => $gymId,
            'gym_name' => $gymName,
            'primary_color' => '#007bff',
            'secondary_color' => '#6c757d',
            'enable_online_booking' => true,
            'enable_online_payments' => true,
            'enable_sms_notifications' => false,
            'enable_email_notifications' => true,
        ]);
    }
}
