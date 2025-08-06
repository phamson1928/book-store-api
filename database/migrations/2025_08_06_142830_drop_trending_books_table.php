<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('trending_books');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // You may recreate the table here if needed, left empty intentionally.
    }
};
