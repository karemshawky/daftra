<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\InventoryItem;
use App\Models\StockTransfer;
use App\Events\LowStockDetected;
use App\Enums\StockTransferStatus;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InsufficientStockException;

class StockTransferService
{
    /**
     * Transfers stock between warehouses in a transactional manner.
     *
     * This method will throw InsufficientStockException if the source warehouse does not have enough stock.
     *
     * @param array $data
     * @return \App\Models\StockTransfer
     * @throws \App\Exceptions\InsufficientStockException
     */
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
                    throw new InsufficientStockException('Insufficient stock in source warehouse.');
                }

                $toStock = Stock::getOrCreateStock($toId, $itemId);

                // Move quantities
                $fromStock->decrement('quantity', $quantity);
                $toStock->increment('quantity', $quantity);

                // Refresh fromStock to get the updated quantity
                $fromStock->refresh();

                // Invalidate cached inventory for both warehouses
                // Fire low stock event if below threshold for source warehouse
                if ($fromStock->quantity < $fromStock->min_stock_level) {
                    event(new LowStockDetected($fromStock));
                }

                // Fire low stock event if below threshold for destination warehouse
                if ($toStock->quantity < $toStock->min_stock_level) {
                    event(new LowStockDetected($toStock));
                }

                return StockTransfer::create([
                    'inventory_item_id' => $itemId,
                    'from_warehouse_id' => $fromId,
                    'to_warehouse_id' => $toId,
                    'quantity' => $quantity,
                    'status' => StockTransferStatus::Completed,
                    'notes' => $data['notes'] ?? null,
                    'transferred_at' => now(),
                ]);
            }
        );
    }
}
