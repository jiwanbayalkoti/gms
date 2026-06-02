<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Old schema used Draft/Scheduled/Published/Failed; app uses draft/published/partial_failed/failed.
   */
  public function up(): void
  {
    if (!Schema::hasTable('social_media_posts') || !Schema::hasColumn('social_media_posts', 'status')) {
      return;
    }

    $driver = Schema::getConnection()->getDriverName();
    if ($driver !== 'mysql') {
      return;
    }

    DB::statement("ALTER TABLE social_media_posts MODIFY status VARCHAR(32) NOT NULL DEFAULT 'draft'");

    $map = [
      'Draft' => 'draft',
      'draft' => 'draft',
      'Scheduled' => 'draft',
      'scheduled' => 'draft',
      'Published' => 'published',
      'published' => 'published',
      'Failed' => 'failed',
      'failed' => 'failed',
      'partial_failed' => 'partial_failed',
    ];

    foreach ($map as $from => $to) {
      DB::table('social_media_posts')->where('status', $from)->update(['status' => $to]);
    }

    DB::table('social_media_posts')
      ->whereNotIn('status', ['draft', 'published', 'partial_failed', 'failed'])
      ->update(['status' => 'draft']);

    DB::statement(
      "ALTER TABLE social_media_posts MODIFY status ENUM('draft', 'published', 'partial_failed', 'failed') NOT NULL DEFAULT 'draft'"
    );
  }

  public function down(): void
  {
    if (!Schema::hasTable('social_media_posts') || !Schema::hasColumn('social_media_posts', 'status')) {
      return;
    }

    $driver = Schema::getConnection()->getDriverName();
    if ($driver !== 'mysql') {
      return;
    }

    DB::statement("ALTER TABLE social_media_posts MODIFY status VARCHAR(32) NOT NULL DEFAULT 'Draft'");

    $map = [
      'draft' => 'Draft',
      'published' => 'Published',
      'partial_failed' => 'Failed',
      'failed' => 'Failed',
    ];

    foreach ($map as $from => $to) {
      DB::table('social_media_posts')->where('status', $from)->update(['status' => $to]);
    }

    DB::statement(
      "ALTER TABLE social_media_posts MODIFY status ENUM('Draft', 'Scheduled', 'Published', 'Failed') NOT NULL DEFAULT 'Draft'"
    );
  }
};
