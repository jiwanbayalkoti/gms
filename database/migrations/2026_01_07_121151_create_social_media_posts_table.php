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
        Schema::create('social_media_posts', function (Blueprint $table) {
            $table->id();
            $table->text('content'); // Post content/text
            $table->string('media_type')->default('text'); // text, image, video
            $table->string('media_url')->nullable(); // URL to image/video
            $table->json('platforms'); // Array of platform IDs to post to
            $table->enum('status', ['Draft', 'Scheduled', 'Published', 'Failed'])->default('Draft');
            $table->timestamp('scheduled_at')->nullable(); // When to post
            $table->timestamp('posted_at')->nullable(); // When it was actually posted
            $table->json('post_results')->nullable(); // Store results from each platform
            $table->text('error_message')->nullable(); // Error if posting failed
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('gym_id')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
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
