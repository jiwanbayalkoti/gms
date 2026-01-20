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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('membership_plan_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // Stripe, PayPal, Cash, etc.
            $table->string('transaction_id')->nullable(); // For online payments
            $table->string('payment_status')->default('Completed'); // Completed, Failed, Refunded
            $table->text('notes')->nullable();
            $table->date('payment_date');
            $table->date('expiry_date')->nullable();
            $table->timestamps();
            
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('membership_plan_id')->references('id')->on('membership_plans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
