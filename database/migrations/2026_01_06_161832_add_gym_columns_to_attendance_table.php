<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds gym_id and created_by to attendance table.
     */
    public function up(): void
    {
        if (!Schema::hasTable('attendance')) {
            return;
        }

        // Add gym_id if it doesn't exist
        if (!Schema::hasColumn('attendance', 'gym_id')) {
            Schema::table('attendance', function (Blueprint $table) {
                $table->unsignedBigInteger('gym_id')->nullable()->after('id');
            });

            // Add foreign key
            try {
                Schema::table('attendance', function (Blueprint $table) {
                    $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
                    $table->index('gym_id');
                });
            } catch (\Exception $e) {
                // Foreign key might fail if gyms table doesn't exist
            }
        }

        // Add created_by if it doesn't exist
        if (!Schema::hasColumn('attendance', 'created_by')) {
            Schema::table('attendance', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->after('gym_id');
            });

            // Add foreign key
            try {
                Schema::table('attendance', function (Blueprint $table) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key might fail
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('attendance')) {
            return;
        }

        Schema::table('attendance', function (Blueprint $table) {
            // Drop foreign keys first
            try {
                if (Schema::hasColumn('attendance', 'gym_id')) {
                    $table->dropForeign(['attendance_gym_id_foreign']);
                }
            } catch (\Exception $e) {
                try {
                    $table->dropForeign(['gym_id']);
                } catch (\Exception $e2) {
                    // Foreign key might not exist
                }
            }

            try {
                if (Schema::hasColumn('attendance', 'created_by')) {
                    $table->dropForeign(['attendance_created_by_foreign']);
                }
            } catch (\Exception $e) {
                try {
                    $table->dropForeign(['created_by']);
                } catch (\Exception $e2) {
                    // Foreign key might not exist
                }
            }

            // Drop columns
            if (Schema::hasColumn('attendance', 'gym_id')) {
                $table->dropColumn('gym_id');
            }
            if (Schema::hasColumn('attendance', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }
};
