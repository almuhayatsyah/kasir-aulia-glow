<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ProductManager extends Component
{
    use WithPagination;

    // ─── Search & Filter ────────────────────────────────────────

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $categoryFilter = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    // ─── Modal State ────────────────────────────────────────────

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingProductId = null;

    public ?int $deletingProductId = null;

    public string $deletingProductName = '';

    // ─── Form Fields ────────────────────────────────────────────

    public string $barcode = '';

    public string $name = '';

    public string $category = '';

    public string $hpp_price = '';

    public string $sell_price = '';

    public string $stock = '';

    public string $exp_date = '';

    // ─── Toast Notification ─────────────────────────────────────

    public string $toastMessage = '';

    public string $toastType = '';

    // ─── Lifecycle ──────────────────────────────────────────────

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    // ─── Sorting ────────────────────────────────────────────────

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // ─── Modal Actions ──────────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingProductId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $this->editingProductId = $product->id;
        $this->barcode = $product->barcode;
        $this->name = $product->name;
        $this->category = $product->category ?? '';
        $this->hpp_price = (string) $product->hpp_price;
        $this->sell_price = (string) $product->sell_price;
        $this->stock = (string) $product->stock;
        $this->exp_date = $product->exp_date?->format('Y-m-d') ?? '';

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function confirmDelete(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->deletingProductId = $product->id;
        $this->deletingProductName = $product->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingProductId = null;
        $this->deletingProductName = '';
    }

    // ─── CRUD Operations ────────────────────────────────────────

    public function save(): void
    {
        $rules = [
            'barcode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'barcode')->ignore($this->editingProductId)->whereNotNull('barcode'),
            ],
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'hpp_price' => 'required|integer|min:0',
            'sell_price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'exp_date' => 'nullable|date',
        ];

        $messages = [
            'barcode.unique' => 'Barcode sudah digunakan produk lain.',
            'name.required' => 'Nama produk wajib diisi.',
            'hpp_price.required' => 'Harga HPP wajib diisi.',
            'hpp_price.integer' => 'Harga HPP harus berupa angka.',
            'hpp_price.min' => 'Harga HPP tidak boleh negatif.',
            'sell_price.required' => 'Harga jual wajib diisi.',
            'sell_price.integer' => 'Harga jual harus berupa angka.',
            'sell_price.min' => 'Harga jual tidak boleh negatif.',
            'stock.required' => 'Stok wajib diisi.',
            'stock.integer' => 'Stok harus berupa angka.',
            'stock.min' => 'Stok tidak boleh negatif.',
            'exp_date.date' => 'Format tanggal kedaluwarsa tidak valid.',
        ];

        $validated = $this->validate($rules, $messages);

        // Jika barcode kosong, auto-generate kode unik
        $barcode = !empty(trim($validated['barcode'] ?? ''))
            ? trim($validated['barcode'])
            : null;

        $data = [
            'barcode' => $barcode,
            'name' => $validated['name'],
            'category' => $validated['category'] ?: null,
            'hpp_price' => (int) $validated['hpp_price'],
            'sell_price' => (int) $validated['sell_price'],
            'stock' => (int) $validated['stock'],
            'exp_date' => $validated['exp_date'] ?: null,
        ];

        if ($this->editingProductId) {
            Product::where('id', $this->editingProductId)->update($data);
            $this->setToast('Produk "' . $data['name'] . '" berhasil diperbarui.', 'success');
        } else {
            Product::create($data);
            $this->setToast('Produk "' . $data['name'] . '" berhasil ditambahkan.', 'success');
        }

        $this->closeModal();
    }

    public function deleteProduct(): void
    {
        if ($this->deletingProductId) {
            try {
                $productName = $this->deletingProductName;
                Product::where('id', $this->deletingProductId)->delete();
                $this->closeDeleteModal();
                $this->setToast('Produk "' . $productName . '" berhasil dihapus.', 'success');
            } catch (\Illuminate\Database\QueryException $e) {
                $this->closeDeleteModal();
                $this->setToast('Gagal dihapus: Produk ini sudah memiliki riwayat transaksi.', 'error');
            }
        } else {
            $this->closeDeleteModal();
        }
    }

    private function setToast(string $message, string $type): void
    {
        $this->toastMessage = $message;
        $this->toastType = $type;
    }

    public function dismissToast(): void
    {
        $this->toastMessage = '';
        $this->toastType = '';
    }

    // ─── Helpers ────────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->barcode = '';
        $this->name = '';
        $this->category = '';
        $this->hpp_price = '';
        $this->sell_price = '';
        $this->stock = '';
        $this->exp_date = '';
        $this->editingProductId = null;
    }

    /** @return array<int, string> */
    private function getCategories(): array
    {
        return Product::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    // ─── Inventory Summary ───────────────────────────────────────

    /** @return array{total_jenis: int, total_stok: int, total_modal: int, total_nilai_jual: int} */
    private function getInventorySummary(): array
    {
        $products = Product::query()->get();

        return [
            'total_jenis'      => $products->count(),
            'total_stok'       => (int) $products->sum('stock'),
            'total_modal'      => (int) $products->sum(fn ($p) => $p->hpp_price * $p->stock),
            'total_nilai_jual' => (int) $products->sum(fn ($p) => $p->sell_price * $p->stock),
        ];
    }

    // ─── Render ─────────────────────────────────────────────────

    public function render(): View
    {
        $products = Product::query()
            ->when($this->search, function ($query): void {
                $query->where(function ($q): void {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('barcode', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($query): void {
                $query->where('category', $this->categoryFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('livewire.product-manager', [
            'products'   => $products,
            'categories' => $this->getCategories(),
            'summary'    => $this->getInventorySummary(),
        ]);
    }
}
