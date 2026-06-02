<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        if (!Schema::hasColumn('settings', 'gym_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unsignedBigInteger('gym_id')->nullable()->after('id');
            });
        }

        if (Schema::hasTable('gyms')) {
            $gymIds = DB::table('gyms')->orderBy('id')->pluck('id');
            $legacy = DB::table('settings')->whereNull('gym_id')->orderBy('id')->first();

            if ($gymIds->isNotEmpty()) {
                if ($legacy) {
                    DB::table('settings')->where('id', $legacy->id)->update(['gym_id' => $gymIds->first()]);
                }

                foreach ($gymIds as $gymId) {
                    if (DB::table('settings')->where('gym_id', $gymId)->exists()) {
                        continue;
                    }

                    $source = DB::table('settings')->orderBy('id')->first();
                    if ($source) {
                        $row = (array) $source;
                        unset($row['id']);
                        $row['gym_id'] = $gymId;
                        $row['created_at'] = now();
                        $row['updated_at'] = now();
                        DB::table('settings')->insert($row);
                    }
                }
            }
        }

        if (Schema::hasColumn('settings', 'gym_id')) {
            try {
                Schema::table('settings', function (Blueprint $table) {
                    $table->unique('gym_id');
                });
            } catch (\Throwable $e) {
                // Unique index may already exist
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings') || !Schema::hasColumn('settings', 'gym_id')) {
            return;
        }

        try {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropUnique(['gym_id']);
            });
        } catch (\Throwable $e) {
            //
        }
    }
};
