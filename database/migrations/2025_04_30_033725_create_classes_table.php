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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('trainer_id');
            $table->integer('capacity');
            $table->integer('current_bookings')->default(0);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('location')->nullable();
            $table->enum('status', ['Active', 'Cancelled', 'Completed'])->default('Active');
            $table->boolean('recurring')->default(false);
            $table->enum('recurring_pattern', ['Daily', 'Weekly', 'Monthly'])->nullable();
            $table->date('recurring_end_date')->nullable();
            $table->timestamps();
            
            $table->foreign('trainer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
