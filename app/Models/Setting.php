<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'enable_email_notifications',
        'enable_pause_feature',
        'minimum_pause_days',
        // Social Media fields removed for now - will be added later
        // 'facebook_app_id',
        // 'facebook_app_secret',
        // 'facebook_access_token',
        // 'facebook_page_id',
        // 'instagram_business_account_id',
        // 'whatsapp_phone_number_id',
        // 'whatsapp_access_token',
        // 'whatsapp_business_account_id',
        // 'youtube_client_id',
        // 'youtube_client_secret',
        // 'youtube_access_token',
        // 'youtube_refresh_token',
        // 'youtube_channel_id',
    ];

    protected $casts = [
        'enable_online_booking' => 'boolean',
        'enable_online_payments' => 'boolean',
        'enable_sms_notifications' => 'boolean',
        'enable_email_notifications' => 'boolean',
        'enable_pause_feature' => 'boolean',
        'minimum_pause_days' => 'integer',
    ];

    /**
     * Get the current settings for the gym (tenant).
     * This will always return the first record or create a default one if none exists.
     */
    public static function current()
    {
        $settings = self::first();
        
        if (!$settings) {
            // Create default settings if none exist
            $settings = self::create([
                'gym_name' => 'My Gym',
                'primary_color' => '#007bff',
                'secondary_color' => '#6c757d',
                'enable_online_booking' => true,
                'enable_online_payments' => true,
                'enable_sms_notifications' => false,
                'enable_email_notifications' => true,
            ]);
        }
        
        return $settings;
    }
}
