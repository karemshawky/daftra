<?php

namespace App\Models;

use App\Filters\StockFilter;
use App\Filters\WarehouseStockFilter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'integer',
        'min_stock_level' => 'integer'
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public static function listStocks(StockFilter $filters, object $request)
    {
        $query = self::with('inventoryItem');   // warehouse: if needed

        return $filters->apply($query, $request)
            ->latest()
            ->paginate()
            ->withQueryString();
    }

    public static function getStock(int $warehouseId, int $itemId): ?Stock
    {
        return self::where('warehouse_id', $warehouseId)
            ->where('inventory_item_id', $itemId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    public static function getOrCreateStock(int $warehouseId, int $itemId): Stock
    {
        return Stock::firstOrCreate(
            [
                'warehouse_id' => $warehouseId,
                'inventory_item_id' => $itemId
            ],
            ['quantity' => 0]
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

    public static function clearWarehousesCache(int $warehouseFrom, int $warehouseTo): void
    {
        Cache::tags(["warehouse_{$warehouseFrom}", "warehouse_{$warehouseTo}"])->flush();
    }
}
