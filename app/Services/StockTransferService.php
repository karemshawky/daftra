<?php

use App\Models\Stock;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    
    public function transferStock(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {
            // Get source stock
            $sourceStock = Stock::getStock(
                $data['from_warehouse_id'],
                $data['inventory_item_id']
            );

            if (!$sourceStock || $sourceStock->available_quantity < $data['quantity']) {
                throw new InsufficientStockException('Insufficient stock in source warehouse');
            }

            // Create transfer record
            $transfer = StockTransfer::create($data);

            // Reserve stock in source warehouse
            $sourceStock->reserve($data['quantity']);

            return $transfer;
        });
    }

    public function completeTransfer(StockTransfer $transfer): bool
    {
        return DB::transaction(function () use ($transfer) {
            // Get stocks
            $sourceStock = Stock::getStock(
                $transfer->from_warehouse_id,
                $transfer->inventory_item_id
            );

            $destStock = Stock::getOrCreateStock(
                $transfer->to_warehouse_id,
                $transfer->inventory_item_id
            );

            // Update quantities
            $sourceStock->quantity -= $transfer->quantity;
            $sourceStock->reserved_quantity -= $transfer->quantity;
            $sourceStock->save();

            $destStock->quantity += $transfer->quantity;
            $destStock->save();

            // Update transfer status
            $transfer->status = StockTransfer::STATUS_COMPLETED;
            $transfer->transferred_at = now();
            $transfer->save();

            return true;
        });
    }

    public function cancelTransfer(StockTransfer $transfer): bool
    {
        return DB::transaction(function () use ($transfer) {
            if ($transfer->status !== StockTransfer::STATUS_PENDING) {
                return false;
            }

            // Release reserved stock
            $sourceStock = Stock::getStock(
                $transfer->from_warehouse_id,
                $transfer->inventory_item_id
            );

            $sourceStock->release($transfer->quantity);

            // Update transfer status
            $transfer->status = StockTransfer::STATUS_CANCELLED;
            $transfer->save();

            return true;
        });
    }
}
