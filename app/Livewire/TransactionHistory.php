<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class TransactionHistory extends Component
{
    use WithPagination;

    // ─── Filters ────────────────────────────────────────────────

    #[Url(as: 'dari')]
    public string $dateFrom = '';

    #[Url(as: 'sampai')]
    public string $dateTo = '';

    #[Url(as: 'q')]
    public string $search = '';

    // ─── Detail Modal ───────────────────────────────────────────

    public bool $showDetail = false;

    public ?int $selectedTransactionId = null;

    /** @var array<int, array<string, mixed>> */
    public array $selectedDetails = [];

    public int $selectedTotalAmount = 0;

    public int $selectedTotalHpp = 0;

    public int $selectedTotalProfit = 0;

    public int $selectedCashReceived = 0;

    public int $selectedCashChange = 0;

    public string $selectedDate = '';

    public string $selectedTime = '';

    public string $activePreset = 'bulan';

    // ─── Lifecycle ──────────────────────────────────────────────

    public function mount(): void
    {
        $this->setPreset('bulan');
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // ─── Date Presets ───────────────────────────────────────────

    public function setPreset(string $preset): void
    {
        $this->activePreset = $preset;
        $this->dateTo = now()->format('Y-m-d');

        $this->dateFrom = match ($preset) {
            'hari' => now()->format('Y-m-d'),
            'minggu' => now()->startOfWeek()->format('Y-m-d'),
            'bulan' => now()->startOfMonth()->format('Y-m-d'),
            'tahun' => now()->startOfYear()->format('Y-m-d'),
            default => now()->startOfMonth()->format('Y-m-d'),
        };

        $this->resetPage();
    }

    // ─── Detail Modal ───────────────────────────────────────────

    public function viewDetail(int $transactionId): void
    {
        $transaction = Transaction::with('details.product')->findOrFail($transactionId);

        $this->selectedTransactionId = $transaction->id;
        $this->selectedTotalAmount = $transaction->total_amount;
        $this->selectedTotalHpp = $transaction->total_hpp;
        $this->selectedTotalProfit = $transaction->total_profit;
        $this->selectedCashReceived = (int) $transaction->cash_received;
        $this->selectedCashChange = (int) $transaction->cash_change;
        $this->selectedDate = $transaction->created_at->format('d/m/Y');
        $this->selectedTime = $transaction->created_at->format('H:i:s');

        $this->selectedDetails = $transaction->details->map(fn ($detail): array => [
            'product_name' => $detail->product?->name ?? 'Produk dihapus',
            'barcode' => $detail->product?->barcode ?? '-',
            'price' => $detail->price,
            'hpp' => $detail->hpp,
            'qty' => $detail->qty,
            'subtotal' => $detail->subtotal,
            'profit' => ($detail->price - $detail->hpp) * $detail->qty,
        ])->toArray();

        $this->showDetail = true;
    }

    public function closeDetail(): void
    {
        $this->showDetail = false;
        $this->selectedTransactionId = null;
        $this->selectedDetails = [];
    }

    public function reprintReceipt(int $transactionId): void
    {
        $this->viewDetail($transactionId);
        $this->dispatch('reprint-receipt');
    }

    // ─── Summary ────────────────────────────────────────────────

    /** @return array{total_transaksi: int, total_penjualan: int, total_profit: int} */
    private function getSummary(): array
    {
        $query = Transaction::query()
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo));

        return [
            'total_transaksi' => $query->count(),
            'total_penjualan' => (int) $query->sum('total_amount'),
            'total_profit' => (int) $query->sum('total_profit'),
        ];
    }

    // ─── Render ─────────────────────────────────────────────────

    public function render(): View
    {
        $transactions = Transaction::query()
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->search, fn ($q) => $q->where('id', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate(20);

        return view('livewire.transaction-history', [
            'transactions' => $transactions,
            'summary' => $this->getSummary(),
        ]);
    }
}
