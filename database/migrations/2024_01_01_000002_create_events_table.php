<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Legacy: events feature removed. Kept so migration history matches existing installs.
 * The events table is dropped by 2026_03_28_120000_drop_posts_and_events_tables if present.
 */
return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
