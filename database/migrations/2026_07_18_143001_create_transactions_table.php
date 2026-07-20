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
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('total_amount')->comment('Total harga jual (Rp)');
            $table->unsignedInteger('total_hpp')->comment('Total harga pokok (Rp)');
            $table->integer('total_profit')->comment('Total keuntungan (Rp)');
            $table->unsignedInteger('cash_received')->nullable()->comment('Uang diterima (Rp)');
            $table->unsignedInteger('cash_change')->nullable()->comment('Uang kembalian (Rp)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
