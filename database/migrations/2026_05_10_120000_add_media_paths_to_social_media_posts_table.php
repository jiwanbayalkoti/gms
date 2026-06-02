<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('social_media_posts')) {
            return;
        }

        Schema::table('social_media_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('social_media_posts', 'media_paths')) {
                $table->json('media_paths')->nullable()->after('content');
            }
        });

        if (Schema::hasColumn('social_media_posts', 'media_path')) {
            $rows = DB::table('social_media_posts')
                ->whereNotNull('media_path')
                ->whereNull('media_paths')
                ->get(['id', 'media_path']);

            foreach ($rows as $row) {
                DB::table('social_media_posts')->where('id', $row->id)->update([
                    'media_paths' => json_encode([$row->media_path]),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('social_media_posts') && Schema::hasColumn('social_media_posts', 'media_paths')) {
            Schema::table('social_media_posts', function (Blueprint $table) {
                $table->dropColumn('media_paths');
            });
        }
    }
};
