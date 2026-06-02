<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('social_media_posts')) {
            return;
        }

        Schema::table('social_media_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('social_media_posts', 'youtube_privacy')) {
                $table->string('youtube_privacy', 20)->default('public')->after('platforms');
            }
            if (!Schema::hasColumn('social_media_posts', 'youtube_format')) {
                $table->string('youtube_format', 20)->default('video')->after('youtube_privacy');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('social_media_posts')) {
            return;
        }

        Schema::table('social_media_posts', function (Blueprint $table) {
            if (Schema::hasColumn('social_media_posts', 'youtube_format')) {
                $table->dropColumn('youtube_format');
            }
            if (Schema::hasColumn('social_media_posts', 'youtube_privacy')) {
                $table->dropColumn('youtube_privacy');
            }
        });
    }
};
