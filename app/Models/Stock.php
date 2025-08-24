<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'inventory_item_id',
        'quantity',
        'reserved_quantity'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer'
    ];

    protected static function booted()
    {
        static::updated(function ($stock) {
            $stock->checkLowStock();
        });
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }

    public function checkLowStock(): void
    {
        if ($this->quantity <= $this->inventoryItem->min_stock_level) {
            event(new LowStockDetected($this));
        }
    }

    public function reserve(int $quantity): bool
    {
        if ($this->available_quantity >= $quantity) {
            $this->reserved_quantity += $quantity;
            return $this->save();
        }
        return false;
    }

    public function release(int $quantity): bool
    {
        $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
        return $this->save();
    }

    ///////////////////////////////////////


    public function getStock(int $warehouseId, int $itemId): ?Stock
    {
        return Stock::where('warehouse_id', $warehouseId)
            ->where('inventory_item_id', $itemId)
            ->first();
    }

    public function getOrCreateStock(int $warehouseId, int $itemId): Stock
    {
        return Stock::firstOrCreate(
            [
                'warehouse_id' => $warehouseId,
                'inventory_item_id' => $itemId
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0
            ]
        );
    }

    // public function getWarehouseInventory(int $warehouseId, array $filters = [])
    // {
    //     $cacheKey = "warehouse_{$warehouseId}_inventory_" . md5(serialize($filters));

    //     return Cache::remember($cacheKey, 300, function () use ($warehouseId, $filters) {
    //         $query = Stock::with(['inventoryItem'])
    //             ->where('warehouse_id', $warehouseId)
    //             ->where('quantity', '>', 0);

    //         // if (!empty($filters['category'])) {
    //         //     $query->whereHas('inventoryItem', function ($q) use ($filters) {
    //         //         $q->where('category', $filters['category']);
    //         //     });
    //         // }

    //         if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
    //             $query->whereHas('inventoryItem', function ($q) use ($filters) {
    //                 $q->priceRange($filters['min_price'] ?? null, $filters['max_price'] ?? null);
    //             });
    //         }

    //         return $query->paginate($filters['per_page'] ?? 15);
    //     });
    // }

    // public function clearWarehouseCache(int $warehouseId): void
    // {
    //     Cache::tags(["warehouse_{$warehouseId}"])->flush();
    // }
}
