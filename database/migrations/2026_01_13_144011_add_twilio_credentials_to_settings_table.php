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
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'sms_provider')) {
                    $table->string('sms_provider')->default('twilio')->after('enable_sms_notifications');
                }
                if (!Schema::hasColumn('settings', 'twilio_account_sid')) {
                    $table->string('twilio_account_sid')->nullable()->after('textlocal_sender_id');
                }
                if (!Schema::hasColumn('settings', 'twilio_auth_token')) {
                    $table->string('twilio_auth_token')->nullable()->after('twilio_account_sid');
                }
                if (!Schema::hasColumn('settings', 'twilio_from_number')) {
                    $table->string('twilio_from_number')->nullable()->after('twilio_auth_token');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'sms_provider')) {
                $table->dropColumn('sms_provider');
            }
            if (Schema::hasColumn('settings', 'twilio_account_sid')) {
                $table->dropColumn('twilio_account_sid');
            }
            if (Schema::hasColumn('settings', 'twilio_auth_token')) {
                $table->dropColumn('twilio_auth_token');
            }
            if (Schema::hasColumn('settings', 'twilio_from_number')) {
                $table->dropColumn('twilio_from_number');
            }
        });
    }
};
