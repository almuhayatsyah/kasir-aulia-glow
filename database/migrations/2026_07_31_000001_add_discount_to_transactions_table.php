<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->decimal('discount_percent', 5, 2)->default(0)->after('cash_change')->comment('Persentase diskon (%)');
            $table->unsignedInteger('discount_amount')->default(0)->after('discount_percent')->comment('Nominal diskon (Rp)');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropColumn(['discount_percent', 'discount_amount']);
        });
    }
};
