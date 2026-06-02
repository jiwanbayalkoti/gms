<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'youtube_token_expires_at')) {
                $table->timestamp('youtube_token_expires_at')->nullable()->after('youtube_refresh_token');
            }
            if (!Schema::hasColumn('settings', 'youtube_client_id')) {
                $table->string('youtube_client_id')->nullable();
            }
            if (!Schema::hasColumn('settings', 'youtube_client_secret')) {
                $table->text('youtube_client_secret')->nullable();
            }
            if (!Schema::hasColumn('settings', 'youtube_refresh_token')) {
                $table->text('youtube_refresh_token')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'youtube_token_expires_at')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('youtube_token_expires_at');
            });
        }
    }
};
