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
                if (!Schema::hasColumn('settings', 'textlocal_api_key')) {
                    $table->string('textlocal_api_key')->nullable()->after('enable_sms_notifications');
                }
                if (!Schema::hasColumn('settings', 'textlocal_sender_id')) {
                    $table->string('textlocal_sender_id')->nullable()->after('textlocal_api_key');
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
            if (Schema::hasColumn('settings', 'textlocal_api_key')) {
                $table->dropColumn('textlocal_api_key');
            }
            if (Schema::hasColumn('settings', 'textlocal_sender_id')) {
                $table->dropColumn('textlocal_sender_id');
            }
        });
    }
};
