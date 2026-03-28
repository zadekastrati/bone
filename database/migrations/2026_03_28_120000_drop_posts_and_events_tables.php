<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('posts');
    }

    public function down(): void
    {
        // Intentionally empty: journal/events feature removed.
    }
};
