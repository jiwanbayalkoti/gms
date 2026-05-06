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
        if (Schema::hasTable('social_media_posts')) {
            return;
        }

        Schema::create('social_media_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('media_path')->nullable();
            $table->string('video_path')->nullable();
            $table->json('platforms');
            $table->json('publish_results')->nullable();
            $table->enum('status', ['draft', 'published', 'partial_failed', 'failed'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_posts');
    }
};

