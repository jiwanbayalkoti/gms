<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include SuperAdmin
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('SuperAdmin', 'GymAdmin', 'Trainer', 'Member') DEFAULT 'Member'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('GymAdmin', 'Trainer', 'Member') DEFAULT 'Member'");
    }
};
