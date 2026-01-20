<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds gym_id and created_by to all gym-specific tables.
     * This ensures data isolation between gyms.
     * Safely handles existing columns.
     */
    public function up(): void
    {
        // Helper function to safely add columns
        $addGymColumns = function($tableName) {
            if (!Schema::hasTable($tableName)) {
                return;
            }
            
            // Check and add gym_id
            if (!Schema::hasColumn($tableName, 'gym_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('gym_id')->nullable()->after('id');
                });
                
                // Add foreign key
                try {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
                    });
                } catch (\Exception $e) {
                    // Foreign key might fail if gyms table doesn't exist yet
                }
                
                // Add index
                try {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->index('gym_id');
                    });
                } catch (\Exception $e) {
                    // Index might already exist
                }
            }
            
            // Check and add created_by
            if (!Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('gym_id');
                });
                
                // Add foreign key
                try {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                    });
                } catch (\Exception $e) {
                    // Foreign key might fail
                }
            }
        };

        // Add to all gym-specific tables
        $tables = [
            'membership_plans',
            'workout_plans',
            'diet_plans',
            'classes',
            'bookings',
            'attendance',  // Note: table name is 'attendance' not 'attendances'
            'payments',
        ];

        foreach ($tables as $tableName) {
            $addGymColumns($tableName);
        }

        // Add to settings table (if exists)
        if (Schema::hasTable('settings')) {
            if (!Schema::hasColumn('settings', 'gym_id')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->unsignedBigInteger('gym_id')->nullable()->after('id');
                });
                
                try {
                    Schema::table('settings', function (Blueprint $table) {
                        $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
                        $table->index('gym_id');
                    });
                } catch (\Exception $e) {
                    // Foreign key might fail
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'membership_plans',
            'workout_plans',
            'diet_plans',
            'classes',
            'bookings',
            'attendance',  // Note: table name is 'attendance' not 'attendances'
            'payments',
            'settings'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Drop foreign keys first
                    try {
                        if (Schema::hasColumn($tableName, 'gym_id')) {
                            $table->dropForeign([$tableName . '_gym_id_foreign']);
                        }
                    } catch (\Exception $e) {
                        // Try alternative foreign key name
                        try {
                            $table->dropForeign(['gym_id']);
                        } catch (\Exception $e2) {
                            // Foreign key might not exist
                        }
                    }
                    
                    try {
                        if (Schema::hasColumn($tableName, 'created_by')) {
                            $table->dropForeign([$tableName . '_created_by_foreign']);
                        }
                    } catch (\Exception $e) {
                        try {
                            $table->dropForeign(['created_by']);
                        } catch (\Exception $e2) {
                            // Foreign key might not exist
                        }
                    }
                    
                    // Drop indexes
                    try {
                        $table->dropIndex([$tableName . '_gym_id_index']);
                    } catch (\Exception $e) {
                        try {
                            $table->dropIndex(['gym_id']);
                        } catch (\Exception $e2) {
                            // Index might not exist
                        }
                    }
                    
                    // Drop columns
                    if (Schema::hasColumn($tableName, 'gym_id')) {
                        $table->dropColumn('gym_id');
                    }
                    if (Schema::hasColumn($tableName, 'created_by')) {
                        $table->dropColumn('created_by');
                    }
                });
            }
        }
    }
};
