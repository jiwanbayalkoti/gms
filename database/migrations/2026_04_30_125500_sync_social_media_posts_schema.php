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
        if (!Schema::hasTable('social_media_posts')) {
            return;
        }

        Schema::table('social_media_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('social_media_posts', 'title')) {
                $table->string('title')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('social_media_posts', 'content')) {
                $table->text('content')->nullable()->after('title');
            }
            if (!Schema::hasColumn('social_media_posts', 'media_path')) {
                $table->string('media_path')->nullable()->after('content');
            }
            if (!Schema::hasColumn('social_media_posts', 'video_path')) {
                $table->string('video_path')->nullable()->after('media_path');
            }
            if (!Schema::hasColumn('social_media_posts', 'platforms')) {
                $table->json('platforms')->nullable()->after('video_path');
            }
            if (!Schema::hasColumn('social_media_posts', 'publish_results')) {
                $table->json('publish_results')->nullable()->after('platforms');
            }
            if (!Schema::hasColumn('social_media_posts', 'status')) {
                $table->enum('status', ['draft', 'published', 'partial_failed', 'failed'])
                    ->default('draft')
                    ->after('publish_results');
            }
            if (!Schema::hasColumn('social_media_posts', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('social_media_posts', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('social_media_posts', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do not drop columns in sync migration.
    }
};

