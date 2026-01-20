<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Facebook/Instagram (Meta) API Keys
            $table->string('facebook_app_id')->nullable()->after('minimum_pause_days');
            $table->text('facebook_app_secret')->nullable();
            $table->text('facebook_access_token')->nullable();
            $table->string('facebook_page_id')->nullable();
            $table->string('instagram_business_account_id')->nullable();
            
            // WhatsApp Business API
            $table->string('whatsapp_phone_number_id')->nullable();
            $table->text('whatsapp_access_token')->nullable();
            $table->string('whatsapp_business_account_id')->nullable();
            
            // YouTube API
            $table->string('youtube_client_id')->nullable();
            $table->text('youtube_client_secret')->nullable();
            $table->text('youtube_access_token')->nullable();
            $table->text('youtube_refresh_token')->nullable();
            $table->string('youtube_channel_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_app_id',
                'facebook_app_secret',
                'facebook_access_token',
                'facebook_page_id',
                'instagram_business_account_id',
                'whatsapp_phone_number_id',
                'whatsapp_access_token',
                'whatsapp_business_account_id',
                'youtube_client_id',
                'youtube_client_secret',
                'youtube_access_token',
                'youtube_refresh_token',
                'youtube_channel_id',
            ]);
        });
    }
};
