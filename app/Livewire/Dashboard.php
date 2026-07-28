<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public string $period = 'hari';

    // ─── Period Preset ──────────────────────────────────────────

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    // ─── Date Range ─────────────────────────────────────────────

    /** @return array{from: Carbon, to: Carbon} */
    private function getDateRange(): array
    {
        $to = Carbon::now();

        $from = match ($this->period) {
            'hari' => Carbon::today(),
            'minggu' => Carbon::now()->startOfWeek(),
            'bulan' => Carbon::now()->startOfMonth(),
            'tahun' => Carbon::now()->startOfYear(),
            default => Carbon::today(),
        };

        return ['from' => $from, 'to' => $to];
    }

    // ─── Summary Stats ──────────────────────────────────────────

    /** @return array{omzet: int, profit: int, transaksi: int, rata_rata: int} */
    private function getSummary(): array
    {
        $range = $this->getDateRange();

        $stats = Transaction::query()
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->selectRaw('COALESCE(SUM(total_amount), 0) as omzet')
            ->selectRaw('COALESCE(SUM(total_profit), 0) as profit')
            ->selectRaw('COUNT(*) as transaksi')
            ->first();

        $omzet = (int) $stats->omzet;
        $transaksi = (int) $stats->transaksi;

        return [
            'omzet' => $omzet,
            'profit' => (int) $stats->profit,
            'transaksi' => $transaksi,
            'rata_rata' => $transaksi > 0 ? (int) round($omzet / $transaksi) : 0,
        ];
    }

    // ─── Daily Sales (last 7 days) ──────────────────────────────

    /** @return array<int, array{date: string, label: string, omzet: int, profit: int}> */
    private function getDailySales(): array
    {
        $days = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days[$date->format('Y-m-d')] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->translatedFormat('D, d/m'),
                'omzet' => 0,
                'profit' => 0,
            ];
        }

        $sales = Transaction::query()
            ->whereBetween('created_at', [Carbon::today()->subDays(6)->startOfDay(), Carbon::now()])
            ->selectRaw("DATE(created_at) as sale_date")
            ->selectRaw('SUM(total_amount) as omzet')
            ->selectRaw('SUM(total_profit) as profit')
            ->groupByRaw('DATE(created_at)')
            ->get();

        foreach ($sales as $sale) {
            $key = $sale->sale_date;
            if (isset($days[$key])) {
                $days[$key]['omzet'] = (int) $sale->omzet;
                $days[$key]['profit'] = (int) $sale->profit;
            }
        }

        return array_values($days);
    }

    // ─── Top Products ───────────────────────────────────────────

    /** @return array<int, array{name: string, qty: int, revenue: int}> */
    private function getTopProducts(): array
    {
        $range = $this->getDateRange();

        return TransactionDetail::query()
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$range['from'], $range['to']])
            ->selectRaw('products.name')
            ->selectRaw('SUM(transaction_details.qty) as qty')
            ->selectRaw('SUM(transaction_details.subtotal) as revenue')
            ->groupBy('products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get()
            ->map(fn ($item): array => [
                'name' => $item->name,
                'qty' => (int) $item->qty,
                'revenue' => (int) $item->revenue,
            ])
            ->toArray();
    }

    // ─── Stock Alerts ───────────────────────────────────────────

    /** @return \Illuminate\Database\Eloquent\Collection<int, Product> */
    private function getLowStockProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('stock', '<=', 5)
            ->orderBy('stock')
            ->limit(10)
            ->get();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Product> */
    private function getExpiredProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('exp_date', '<', Carbon::today())
            ->orderBy('exp_date')
            ->limit(10)
            ->get();
    }

    // ─── Product & Transaction Counts ───────────────────────────

    /** @return array{total_produk: int, total_stok: int, total_modal: int, total_nilai_jual: int} */
    private function getInventoryStats(): array
    {
        $products = Product::query()->get();

        return [
            'total_produk'     => $products->count(),
            'total_stok'       => (int) $products->sum('stock'),
            'total_modal'      => (int) $products->sum(fn ($p) => $p->hpp_price * $p->stock),
            'total_nilai_jual' => (int) $products->sum(fn ($p) => $p->sell_price * $p->stock),
        ];
    }

    // ─── Render ─────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.dashboard', [
            'summary' => $this->getSummary(),
            'dailySales' => $this->getDailySales(),
            'topProducts' => $this->getTopProducts(),
            'lowStockProducts' => $this->getLowStockProducts(),
            'expiredProducts' => $this->getExpiredProducts(),
            'inventoryStats' => $this->getInventoryStats(),
        ]);
    }
}
