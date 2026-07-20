<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'barcode',
        'name',
        'category',
        'hpp_price',
        'sell_price',
        'stock',
        'exp_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hpp_price' => 'integer',
            'sell_price' => 'integer',
            'stock' => 'integer',
            'exp_date' => 'date',
        ];
    }

    /**
     * @return HasMany<TransactionDetail, $this>
     */
    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
