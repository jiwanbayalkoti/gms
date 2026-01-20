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
        Schema::create('salary_deductions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_payment_id');
            $table->string('deduction_type'); // e.g., 'Tax', 'Insurance', 'Loan', 'Other'
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->foreign('salary_payment_id')->references('id')->on('salary_payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_deductions');
    }
};
