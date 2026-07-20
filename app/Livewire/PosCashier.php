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
class PosCashier extends Component
{
    public string $search = '';

    public string $selectedCategory = '';

    /** @var array<int, array{product_id: int, name: string, barcode: string, price: int, hpp: int, qty: int, subtotal: int, max_stock: int}> */
    public array $cart = [];

    public int $totalAmount = 0;

    public bool $showPaymentModal = false;

    public string $cashReceivedInput = '';

    public int $cashReceived = 0;

    public int $changeAmount = 0;

    public string $flashMessage = '';

    public string $flashType = 'success';

    // ─── Scan Barcode & Process Search ──────────────────────────

    public function processSearch(): void
    {
        $term = trim($this->search);

        if ($term === '') {
            return;
        }

        // 1. Cari berdasarkan kode barcode eksak
        $product = Product::where('barcode', $term)->first();

        // 2. Jika tidak ada kecocokan barcode eksak, coba cari apakah nama produknya unik (hanya ada 1 hasil pencarian)
        if (! $product) {
            $matchingProducts = Product::where('name', 'like', '%' . $term . '%')
                ->where('stock', '>', 0)
                ->get();

            if ($matchingProducts->count() === 1) {
                $product = $matchingProducts->first();
            }
        }

        if ($product) {
            if ($product->stock <= 0) {
                $this->setFlash('Stok "' . $product->name . '" habis.', 'error');
                return;
            }

            if ($product->exp_date !== null && $product->exp_date->isBefore(Carbon::today())) {
                $this->setFlash('"' . $product->name . '" sudah kedaluwarsa.', 'error');
                return;
            }

            $this->addToCart($product);
            $this->search = '';
        }

        $this->dispatch('scan-barcode-done');
    }

    // ─── Add Product by ID (Manual Tap) ─────────────────────────

    public function addProductById(int $productId): void
    {
        $product = Product::find($productId);

        if (! $product) {
            $this->setFlash('Produk tidak ditemukan.', 'error');
            return;
        }

        if ($product->stock <= 0) {
            $this->setFlash('Stok "' . $product->name . '" habis.', 'error');
            return;
        }

        if ($product->exp_date !== null && $product->exp_date->isBefore(Carbon::today())) {
            $this->setFlash('"' . $product->name . '" sudah kedaluwarsa.', 'error');
            return;
        }

        $this->addToCart($product);
        $this->dispatch('scan-barcode-done'); // to trigger autofocus on barcode input
    }

    // ─── Category Selection ─────────────────────────────────────

    public function selectCategory(string $category): void
    {
        $this->selectedCategory = $category;
    }

    // ─── Cart Operations ────────────────────────────────────────

    private function addToCart(Product $product): void
    {
        $productId = $product->id;

        if (isset($this->cart[$productId])) {
            $currentQty = $this->cart[$productId]['qty'];

            if ($currentQty >= $product->stock) {
                $this->setFlash('Stok "' . $product->name . '" tidak cukup (sisa: ' . $product->stock . ').', 'error');
                return;
            }

            $this->cart[$productId]['qty']++;
            $this->cart[$productId]['subtotal'] = $this->cart[$productId]['price'] * $this->cart[$productId]['qty'];
        } else {
            $this->cart[$productId] = [
                'product_id' => $productId,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'price' => $product->sell_price,
                'hpp' => $product->hpp_price,
                'qty' => 1,
                'subtotal' => $product->sell_price,
                'max_stock' => $product->stock,
            ];
        }

        $this->calculateTotal();
        $this->setFlash('"' . $product->name . '" ditambahkan ke keranjang.', 'success');
    }

    public function incrementQty(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $item = $this->cart[$productId];

        if ($item['qty'] >= $item['max_stock']) {
            $this->setFlash('Stok "' . $item['name'] . '" tidak cukup (sisa: ' . $item['max_stock'] . ').', 'error');
            return;
        }

        $this->cart[$productId]['qty']++;
        $this->cart[$productId]['subtotal'] = $this->cart[$productId]['price'] * $this->cart[$productId]['qty'];
        $this->calculateTotal();
        $this->dispatch('scan-barcode-done');
    }

    public function decrementQty(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $this->cart[$productId]['qty']--;

        if ($this->cart[$productId]['qty'] <= 0) {
            unset($this->cart[$productId]);
        } else {
            $this->cart[$productId]['subtotal'] = $this->cart[$productId]['price'] * $this->cart[$productId]['qty'];
        }

        $this->calculateTotal();
        $this->dispatch('scan-barcode-done');
    }

    public function removeItem(int $productId): void
    {
        unset($this->cart[$productId]);
        $this->calculateTotal();
        $this->dispatch('scan-barcode-done');
    }

    private function calculateTotal(): void
    {
        $this->totalAmount = 0;

        foreach ($this->cart as $item) {
            $this->totalAmount += $item['subtotal'];
        }
    }

    // ─── Payment Modal & Calculations ──────────────────────────

    public function openPaymentModal(): void
    {
        if (empty($this->cart)) {
            $this->setFlash('Keranjang kosong, tidak bisa bayar.', 'error');
            return;
        }

        $this->cashReceivedInput = '';
        $this->cashReceived = 0;
        $this->changeAmount = 0;
        $this->showPaymentModal = true;

        $this->dispatch('focus-cash-received');
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->dispatch('scan-barcode-done'); // Refocus main search input
    }

    public function updatedCashReceivedInput(): void
    {
        // Filter out non-numeric characters
        $clean = preg_replace('/[^0-9]/', '', $this->cashReceivedInput) ?? '';
        $this->cashReceivedInput = $clean;
        $this->cashReceived = $clean !== '' ? (int) $clean : 0;
        $this->changeAmount = max(0, $this->cashReceived - $this->totalAmount);
    }

    public function selectQuickCash(int $amount): void
    {
        $this->cashReceived = $amount;
        $this->cashReceivedInput = (string) $amount;
        $this->changeAmount = max(0, $amount - $this->totalAmount);
    }

    // ─── Checkout ───────────────────────────────────────────────

    public function checkout(): void
    {
        if (empty($this->cart)) {
            $this->setFlash('Keranjang kosong, tidak bisa checkout.', 'error');
            return;
        }

        if ($this->cashReceived < $this->totalAmount) {
            $this->setFlash('Pembayaran kurang. Uang yang diterima belum mencukupi.', 'error');
            return;
        }

        DB::transaction(function (): void {
            $totalAmount = 0;
            $totalHpp = 0;

            foreach ($this->cart as $item) {
                $totalAmount += $item['price'] * $item['qty'];
                $totalHpp += $item['hpp'] * $item['qty'];
            }

            $totalProfit = $totalAmount - $totalHpp;

            $transaction = Transaction::create([
                'total_amount' => $totalAmount,
                'total_hpp' => $totalHpp,
                'total_profit' => $totalProfit,
                'cash_received' => $this->cashReceived,
                'cash_change' => $this->changeAmount,
            ]);

            foreach ($this->cart as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'price' => $item['price'],
                    'hpp' => $item['hpp'],
                    'qty' => $item['qty'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);

                Product::where('id', $item['product_id'])
                    ->decrement('stock', $item['qty']);
            }
        });

        $this->dispatch('checkout-success',
            total: $this->totalAmount,
            received: $this->cashReceived,
            change: $this->changeAmount,
            items: array_values($this->cart)
        );

        $this->showPaymentModal = false;
        $this->cart = [];
        $this->totalAmount = 0;
        $this->cashReceived = 0;
        $this->cashReceivedInput = '';
        $this->changeAmount = 0;
        $this->setFlash('Transaksi berhasil! Struk sedang dicetak...', 'success');
        $this->dispatch('scan-barcode-done'); // Refocus main search input
    }

    // ─── Helpers ────────────────────────────────────────────────

    private function setFlash(string $message, string $type): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }

    public function clearFlash(): void
    {
        $this->flashMessage = '';
    }

    // ─── Render ─────────────────────────────────────────────────

    public function render(): View
    {
        $products = Product::query()
            ->when($this->search, function ($query): void {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('barcode', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedCategory, function ($query): void {
                $query->where('category', $this->selectedCategory);
            })
            ->orderBy('name')
            ->get();

        $categories = Product::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        return view('livewire.pos-cashier', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
