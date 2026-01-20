<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds gym_id to users table.
     * - NULL for SuperAdmin (can access all gyms)
     * - gym_id for all other users (belong to one gym)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add gym_id column (nullable for SuperAdmin) - only if it doesn't exist
            if (!Schema::hasColumn('users', 'gym_id')) {
                $table->unsignedBigInteger('gym_id')->nullable()->after('role');
            }
        });
        
        // Add foreign key and index separately
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'gym_id')) {
                try {
                    $table->foreign('gym_id')
                          ->references('id')
                          ->on('gyms')
                          ->onDelete('cascade')
                          ->onUpdate('cascade');
                } catch (\Exception $e) {
                    // Foreign key might fail if gyms table doesn't exist yet
                }
                
                try {
                    $table->index('gym_id');
                } catch (\Exception $e) {
                    // Index might already exist
                }
            }
        });
        
        // Update role enum to include SuperAdmin, Staff
        // Check current enum values first
        try {
            $currentRoles = DB::select("SHOW COLUMNS FROM users WHERE Field = 'role'");
            if (!empty($currentRoles)) {
                $enumValues = $currentRoles[0]->Type;
                // Only update if SuperAdmin or Staff not in enum
                if (strpos($enumValues, 'SuperAdmin') === false || strpos($enumValues, 'Staff') === false) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->enum('role', ['SuperAdmin', 'GymAdmin', 'Trainer', 'Staff', 'Member'])
                              ->default('Member')
                              ->change();
                    });
                }
            }
        } catch (\Exception $e) {
            // If check fails, try to update anyway
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->enum('role', ['SuperAdmin', 'GymAdmin', 'Trainer', 'Staff', 'Member'])
                          ->default('Member')
                          ->change();
                });
            } catch (\Exception $e2) {
                // Migration might have already run
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['gym_id']);
            $table->dropIndex(['gym_id']);
            $table->dropColumn('gym_id');
            
            // Revert role enum
            $table->enum('role', ['GymAdmin', 'Trainer', 'Member'])
                  ->default('Member')
                  ->change();
        });
    }
};

