<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->integer('weight_in_grams')->nullable();
            $table->string('packaging_size_cm')->nullable();
            $table->integer('number_of_pages')->nullable();
            $table->string('form')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['weight_in_grams', 'packaging_size_cm', 'number_of_pages', 'form']);
        });
    }
};
