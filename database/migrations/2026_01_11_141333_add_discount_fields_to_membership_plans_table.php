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
        if (Schema::hasTable('membership_plans')) {
        Schema::table('membership_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('membership_plans', 'has_discount')) {
                    $table->boolean('has_discount')->default(false);
                }
                if (!Schema::hasColumn('membership_plans', 'discount_percentage')) {
                    $table->decimal('discount_percentage', 5, 2)->nullable();
                }
                if (!Schema::hasColumn('membership_plans', 'discount_amount')) {
                    $table->decimal('discount_amount', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('membership_plans', 'discount_start_date')) {
                    $table->date('discount_start_date')->nullable();
                }
                if (!Schema::hasColumn('membership_plans', 'discount_end_date')) {
                    $table->date('discount_end_date')->nullable();
                }
                if (!Schema::hasColumn('membership_plans', 'discount_description')) {
                    $table->text('discount_description')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->dropColumn([
                'has_discount',
                'discount_percentage',
                'discount_amount',
                'discount_start_date',
                'discount_end_date',
                'discount_description'
            ]);
        });
    }
};
