<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\FilterInventoryItem;
use App\Filters\InventoryItem\InventoryItemFilter;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
        'min_stock_level' => 'integer'
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class);
    }

    public function scopeFilter(Builder $query, InventoryItemFilter $filters, FilterInventoryItem $request)
    {
        return $filters->apply($query, $request)
            ->latest()
            ->paginate()
            ->withQueryString();
    }
}
