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
                if (!Schema::hasColumn('settings', 'sparrow_sms_token')) {
                    $table->string('sparrow_sms_token')->nullable()->after('twilio_from_number');
                }
                if (!Schema::hasColumn('settings', 'sparrow_sms_from')) {
                    $table->string('sparrow_sms_from')->nullable()->after('sparrow_sms_token');
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
            if (Schema::hasColumn('settings', 'sparrow_sms_token')) {
                $table->dropColumn('sparrow_sms_token');
            }
            if (Schema::hasColumn('settings', 'sparrow_sms_from')) {
                $table->dropColumn('sparrow_sms_from');
            }
        });
    }
};
