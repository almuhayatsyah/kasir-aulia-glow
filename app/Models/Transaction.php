<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'total_amount',
        'total_hpp',
        'total_profit',
        'cash_received',
        'cash_change',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'total_hpp' => 'integer',
            'total_profit' => 'integer',
            'cash_received' => 'integer',
            'cash_change' => 'integer',
        ];
    }

    /**
     * @return HasMany<TransactionDetail, $this>
     */
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
