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
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('payment_period_start');
            $table->date('payment_period_end');
            $table->decimal('base_amount', 10, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->nullable()->default(0);
            $table->decimal('bonus_amount', 10, 2)->nullable()->default(0);
            $table->decimal('deductions', 10, 2)->nullable()->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->enum('payment_method', ['Cash', 'Bank Transfer', 'Cheque', 'Online'])->default('Bank Transfer');
            $table->enum('payment_status', ['Pending', 'Paid', 'Failed', 'Cancelled'])->default('Pending');
            $table->date('payment_date')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('gym_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('salary_id')->references('id')->on('salaries')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['gym_id', 'payment_status']);
            $table->index(['employee_id', 'payment_period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
