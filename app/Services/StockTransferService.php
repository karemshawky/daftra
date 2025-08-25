<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\InventoryItem;
use App\Models\StockTransfer;
use App\Events\LowStockDetected;
use Illuminate\Http\JsonResponse;
use App\Enums\StockTransferStatus;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    public function transferStock(array $data)
    {
        return DB::transaction(
            function () use ($data) {

                // Lock both stock rows FOR UPDATE to avoid race conditions
                $itemId = $data['inventory_item_id'];
                $fromId = $data['from_warehouse_id'];
                $toId = $data['to_warehouse_id'];
                $quantity = (int) $data['quantity'];

                $item = InventoryItem::findOrFail($data['inventory_item_id']);

                $fromStock = Stock::getStock($fromId, $itemId);

                if (!$fromStock || $fromStock->quantity < $quantity) {
                    abort(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Insufficient stock in source warehouse.');
                }

                $toStock = Stock::getOrCreateStock($toId, $itemId);

                if (!$toStock) {
                    $toStock = Stock::create([
                        'inventory_item_id' => $itemId,
                        'warehouse_id' => $toId,
                        'quantity' => 0,
                    ]);
                }

                // Move quantities
                $fromStock->decrement('quantity', $quantity);
                $toStock->increment('quantity', $quantity);

                // Invalidate cached inventory for both warehouses
                Stock::clearWarehouseCache($fromId);
                Stock::clearWarehouseCache($toId);

                // Fire low stock event if below threshold
                if ($fromStock->quantity < $fromStock->min_stock_level) {
                    event(new LowStockDetected($fromStock, $item));
                }

                return StockTransfer::create([
                    'inventory_item_id' => $itemId,
                    'from_warehouse_id' => $fromId,
                    'to_warehouse_id' => $toId,
                    'quantity' => $quantity,
                    'status' => StockTransferStatus::Completed,
                    'notes' => $data['notes'] ?? null,
                    'transferred_at'=> now(),
                ]);
            }
        );
    }
}
