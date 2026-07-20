<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'transaction_id',
        'product_id',
        'price',
        'hpp',
        'qty',
        'subtotal',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_id' => 'integer',
            'product_id' => 'integer',
            'price' => 'integer',
            'hpp' => 'integer',
            'qty' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Transaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
