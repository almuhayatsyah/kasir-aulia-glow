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
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('barcode')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->unsignedInteger('hpp_price')->comment('Harga Pokok Penjualan (Rp)');
            $table->unsignedInteger('sell_price')->comment('Harga Jual (Rp)');
            $table->unsignedInteger('stock')->default(0);
            $table->date('exp_date')->nullable();
            $table->timestamps();

            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
