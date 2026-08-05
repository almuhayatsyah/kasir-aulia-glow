<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        private readonly string $dateFrom = '',
        private readonly string $dateTo = '',
        private readonly string $search = '',
    ) {}

    public function collection(): Collection
    {
        return Transaction::query()
            ->with(['details.product'])
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->search, function ($q): void {
                $q->where(function ($sub): void {
                    $sub->where('id', 'like', '%' . $this->search . '%')
                        ->orWhereHas('details.product', fn ($p) => $p->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->latest()
            ->get()
            ->flatMap(function (Transaction $trx): array {
                $rows = [];
                foreach ($trx->details as $detail) {
                    $rows[] = [
                        'transaction' => $trx,
                        'detail'      => $detail,
                    ];
                }

                // If no details, still show the transaction
                if (empty($rows)) {
                    $rows[] = ['transaction' => $trx, 'detail' => null];
                }

                return $rows;
            });
    }

    /** @param array{transaction: Transaction, detail: mixed} $row */
    public function map($row): array
    {
        $trx    = $row['transaction'];
        $detail = $row['detail'];

        return [
            '#' . str_pad((string) $trx->id, 5, '0', STR_PAD_LEFT),
            $trx->created_at->format('d/m/Y'),
            $trx->created_at->format('H:i:s'),
            $detail?->product?->name ?? '-',
            $detail?->qty ?? '-',
            $detail ? $detail->price : '-',
            $detail ? ($detail->price * $detail->qty) : '-',
            $trx->total_amount,
            $trx->total_hpp,
            $trx->total_profit,
            $trx->cash_received ?? 0,
            $trx->cash_change ?? 0,
        ];
    }

    public function headings(): array
    {
        return [
            'No. Transaksi',
            'Tanggal',
            'Waktu',
            'Nama Produk',
            'Qty',
            'Harga Satuan (Rp)',
            'Subtotal Produk (Rp)',
            'Total Transaksi (Rp)',
            'HPP (Rp)',
            'Profit (Rp)',
            'Uang Diterima (Rp)',
            'Kembalian (Rp)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'EC4899'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Riwayat Transaksi';
    }
}
