<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Sheet;

class SalesReportExport implements WithMultipleSheets
{
    public function __construct(
        private readonly string $dateFrom,
        private readonly string $dateTo,
    ) {}

    /** @return array<int, mixed> */
    public function sheets(): array
    {
        return [
            new SalesReportSummarySheet($this->dateFrom, $this->dateTo),
            new SalesReportDailySheet($this->dateFrom, $this->dateTo),
            new SalesReportTopProductsSheet($this->dateFrom, $this->dateTo),
        ];
    }
}

// ─── Sheet 1: Ringkasan ────────────────────────────────────────────────────

class SalesReportSummarySheet implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\ShouldAutoSize
{
    public function __construct(
        private readonly string $dateFrom,
        private readonly string $dateTo,
    ) {}

    /** @return array<int, array<int, mixed>> */
    public function array(): array
    {
        $stats = Transaction::query()
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->selectRaw('COALESCE(SUM(total_amount), 0) as omzet')
            ->selectRaw('COALESCE(SUM(total_hpp), 0) as hpp')
            ->selectRaw('COALESCE(SUM(total_profit), 0) as profit')
            ->selectRaw('COUNT(*) as transaksi')
            ->first();

        $omzet     = (int) $stats?->omzet;
        $transaksi = (int) $stats?->transaksi;
        $rataRata  = $transaksi > 0 ? (int) round($omzet / $transaksi) : 0;

        return [
            ['LAPORAN PENJUALAN - AULIA GLOW'],
            [''],
            ['Periode', Carbon::parse($this->dateFrom)->format('d M Y') . ' s/d ' . Carbon::parse($this->dateTo)->format('d M Y')],
            ['Dicetak', now()->format('d M Y, H:i') . ' WIB'],
            [''],
            ['RINGKASAN KEUANGAN'],
            [''],
            ['Metrik', 'Nilai (Rp)'],
            ['Total Omzet', $omzet],
            ['Total HPP', (int) $stats?->hpp],
            ['Total Profit', (int) $stats?->profit],
            ['Jumlah Transaksi', $transaksi],
            ['Rata-rata per Transaksi', $rataRata],
            [''],
            ['Margin Profit (%)', $omzet > 0 ? round(((int) $stats?->profit / $omzet) * 100, 2) : 0],
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            6 => ['font' => ['bold' => true, 'color' => ['rgb' => 'EC4899']]],
            8 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EC4899']]],
        ];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }
}

// ─── Sheet 2: Breakdown Harian ─────────────────────────────────────────────

class SalesReportDailySheet implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithStyles
{
    public function __construct(
        private readonly string $dateFrom,
        private readonly string $dateTo,
    ) {}

    public function collection(): \Illuminate\Support\Collection
    {
        return Transaction::query()
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->selectRaw('DATE(created_at) as sale_date')
            ->selectRaw('SUM(total_amount) as omzet')
            ->selectRaw('SUM(total_hpp) as hpp')
            ->selectRaw('SUM(total_profit) as profit')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('sale_date')
            ->orderBy('sale_date', 'desc')
            ->get();
    }

    /** @param mixed $row */
    public function map($row): array
    {
        return [
            Carbon::parse($row->sale_date)->format('d/m/Y'),
            (int) $row->count,
            (int) $row->omzet,
            (int) $row->hpp,
            (int) $row->profit,
            $row->omzet > 0 ? round(($row->profit / $row->omzet) * 100, 2) : 0,
        ];
    }

    public function headings(): array
    {
        return ['Tanggal', 'Jml Transaksi', 'Omzet (Rp)', 'HPP (Rp)', 'Profit (Rp)', 'Margin (%)'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EC4899']]],
        ];
    }

    public function title(): string
    {
        return 'Breakdown Harian';
    }
}

// ─── Sheet 3: Top Produk ───────────────────────────────────────────────────

class SalesReportTopProductsSheet implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithStyles
{
    public function __construct(
        private readonly string $dateFrom,
        private readonly string $dateTo,
    ) {}

    public function collection(): \Illuminate\Support\Collection
    {
        return TransactionDetail::query()
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereDate('transactions.created_at', '>=', $this->dateFrom)
            ->whereDate('transactions.created_at', '<=', $this->dateTo)
            ->selectRaw('products.name')
            ->selectRaw('SUM(transaction_details.qty) as qty')
            ->selectRaw('SUM(transaction_details.subtotal) as subtotal')
            ->selectRaw('SUM(transaction_details.qty * (transaction_details.price - transaction_details.hpp)) as profit')
            ->groupBy('products.name')
            ->orderByDesc('qty')
            ->get();
    }

    /** @param mixed $row */
    public function map($row): array
    {
        return [
            $row->name,
            (int) $row->qty,
            (int) $row->subtotal,
            (int) $row->profit,
        ];
    }

    public function headings(): array
    {
        return ['Nama Produk', 'Total Terjual (pcs)', 'Total Omzet (Rp)', 'Total Profit (Rp)'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EC4899']]],
        ];
    }

    public function title(): string
    {
        return 'Top Produk';
    }
}
