<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SalesReport extends Component
{
    #[Url(as: 'dari')]
    public string $dateFrom = '';

    #[Url(as: 'sampai')]
    public string $dateTo = '';

    // ─── Lifecycle ──────────────────────────────────────────────

    public function mount(): void
    {
        if ($this->dateFrom === '') {
            $this->dateFrom = now()->format('Y-m-d');
        }
        if ($this->dateTo === '') {
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    // ─── Presets ────────────────────────────────────────────────

    public function setPreset(string $preset): void
    {
        $this->dateTo = now()->format('Y-m-d');

        $this->dateFrom = match ($preset) {
            'hari' => now()->format('Y-m-d'),
            'minggu' => now()->startOfWeek()->format('Y-m-d'),
            'bulan' => now()->startOfMonth()->format('Y-m-d'),
            'tahun' => now()->startOfYear()->format('Y-m-d'),
            default => now()->format('Y-m-d'),
        };
    }

    // ─── Query Helpers ──────────────────────────────────────────

    /** @return array{omzet: int, hpp: int, profit: int, transaksi: int, rata_rata: int} */
    private function getSummaryStats(): array
    {
        $stats = Transaction::query()
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->selectRaw('COALESCE(SUM(total_amount), 0) as omzet')
            ->selectRaw('COALESCE(SUM(total_hpp), 0) as hpp')
            ->selectRaw('COALESCE(SUM(total_profit), 0) as profit')
            ->selectRaw('COUNT(*) as transaksi')
            ->first();

        $omzet = (int) $stats->omzet;
        $transaksi = (int) $stats->transaksi;

        return [
            'omzet' => $omzet,
            'hpp' => (int) $stats->hpp,
            'profit' => (int) $stats->profit,
            'transaksi' => $transaksi,
            'rata_rata' => $transaksi > 0 ? (int) round($omzet / $transaksi) : 0,
        ];
    }

    /** @return array<int, array{name: string, qty: int, subtotal: int}> */
    private function getTopProducts(): array
    {
        return TransactionDetail::query()
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereDate('transactions.created_at', '>=', $this->dateFrom)
            ->whereDate('transactions.created_at', '<=', $this->dateTo)
            ->selectRaw('products.name')
            ->selectRaw('SUM(transaction_details.qty) as qty')
            ->selectRaw('SUM(transaction_details.subtotal) as subtotal')
            ->groupBy('products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get()
            ->map(fn ($item): array => [
                'name' => $item->name,
                'qty' => (int) $item->qty,
                'subtotal' => (int) $item->subtotal,
            ])
            ->toArray();
    }

    /** @return array<int, array{date: string, omzet: int, profit: int, count: int}> */
    private function getDailyBreakdown(): array
    {
        return Transaction::query()
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->selectRaw('DATE(created_at) as sale_date')
            ->selectRaw('SUM(total_amount) as omzet')
            ->selectRaw('SUM(total_profit) as profit')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('sale_date')
            ->orderBy('sale_date', 'desc')
            ->get()
            ->map(fn ($item): array => [
                'date' => Carbon::parse($item->sale_date)->format('d/m/Y'),
                'omzet' => (int) $item->omzet,
                'profit' => (int) $item->profit,
                'count' => (int) $item->count,
            ])
            ->toArray();
    }

    // ─── Render ─────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.sales-report', [
            'summary' => $this->getSummaryStats(),
            'topProducts' => $this->getTopProducts(),
            'dailyBreakdown' => $this->getDailyBreakdown(),
        ]);
    }
}
