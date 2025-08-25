<?php

namespace App\Models;

use App\Filters\StockFilter;
use App\Events\LowStockDetected;
use App\Filters\WarehouseStockFilter;
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

    /**
     * Reserves a certain quantity of stock from this stock item.
     * This will increase the reserved quantity by the given amount.
     * If the available quantity is not enough to fulfill the request, this method will return false.
     * @param int $quantity The quantity of stock to reserve.
     * @return bool Whether the stock was successfully reserved.
     */
    public function reserve(int $quantity): bool
    {
        if ($this->available_quantity >= $quantity) {
            $this->reserved_quantity += $quantity;
            return $this->save();
        }
        return false;
    }

    /**
     * Releases a certain quantity of stock from this stock item.
     * This will decrease the reserved quantity by the given amount.
     * The reserved quantity will never go below 0.
     * @param int $quantity The quantity of stock to release.
     * @return bool Whether the stock was successfully released.
     */
    public function release(int $quantity): bool
    {
        $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
        return $this->save();
    }

    ///////////////////////////////////////


    public static function getStock(int $warehouseId, int $itemId): ?Stock
    {
        return self::where('warehouse_id', $warehouseId)
            ->where('inventory_item_id', $itemId)
            ->first();
    }

    public function getOrCreateStock(int $warehouseId, int $itemId): Stock
    {
        return self::firstOrCreate(
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

    public static function getWarehouseInventory(Warehouse $warehouse, WarehouseStockFilter $filters, object $request)
    {
        $cacheKey = "warehouse_{$warehouse->id}_inventory_" . md5(serialize($filters));

        return Cache::remember($cacheKey, 300, function () use ($warehouse, $filters, $request) {

            $query = self::with(['inventoryItem'])
                ->where('warehouse_id', $warehouse->id)
                ->where('quantity', '>', 0);

            return $filters->apply($query, $request)
                ->latest()
                ->paginate()
                ->withQueryString();
        });
    }

    public function clearWarehouseCache(int $warehouseId): void
    {
        Cache::tags(["warehouse_{$warehouseId}"])->flush();
    }

    ///////////////////////////////////////

    public static function listStocks(StockFilter $filters, object $request)
    {
        $query = self::with('inventoryItem');   // warehouse: if needed

        return $filters->apply($query, $request)
            ->latest()
            ->paginate()
            ->withQueryString();
    }
}
