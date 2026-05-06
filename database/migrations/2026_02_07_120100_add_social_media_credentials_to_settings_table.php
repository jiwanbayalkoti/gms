<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'facebook_page_access_token')) {
                $table->text('facebook_page_access_token')->nullable()->after('sparrow_sms_from');
            }
            if (!Schema::hasColumn('settings', 'facebook_page_id')) {
                $table->string('facebook_page_id')->nullable()->after('facebook_page_access_token');
            }
            if (!Schema::hasColumn('settings', 'instagram_business_account_id')) {
                $table->string('instagram_business_account_id')->nullable()->after('facebook_page_id');
            }
            if (!Schema::hasColumn('settings', 'youtube_access_token')) {
                $table->text('youtube_access_token')->nullable()->after('instagram_business_account_id');
            }
            if (!Schema::hasColumn('settings', 'youtube_channel_id')) {
                $table->string('youtube_channel_id')->nullable()->after('youtube_access_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach ([
                'facebook_page_access_token',
                'facebook_page_id',
                'instagram_business_account_id',
                'youtube_access_token',
                'youtube_channel_id',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

