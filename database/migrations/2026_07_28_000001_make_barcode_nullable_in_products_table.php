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
        Schema::table('products', function (Blueprint $table): void {
            // Drop the unique index first before changing the column
            $table->dropUnique(['barcode']);
            // Make barcode nullable and unique (null values are allowed)
            $table->string('barcode', 50)->nullable()->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('barcode', 50)->nullable(false)->unique()->change();
        });
    }
};
