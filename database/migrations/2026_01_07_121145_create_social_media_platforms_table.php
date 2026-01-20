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
        Schema::create('social_media_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Facebook, Instagram, WhatsApp, YouTube
            $table->string('slug')->unique(); // facebook, instagram, whatsapp, youtube
            $table->string('icon')->nullable(); // Font Awesome icon class
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('required_credentials')->nullable(); // Store required credential fields
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_platforms');
    }
};
