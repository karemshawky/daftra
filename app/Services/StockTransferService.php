<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockTransfer;
use App\Enums\StockTransferStatus;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InsufficientStockException;

class StockTransferService
{

    /**
     * Transfers stock from one warehouse to another.
     *
     * @param array $data Stock transfer data.
     *  - from_warehouse_id: ID of the source warehouse.
     *  - to_warehouse_id: ID of the destination warehouse.
     *  - inventory_item_id: ID of the inventory item to transfer.
     *  - quantity: Quantity of stock to transfer.
     *
     * @return StockTransfer The stock transfer record.
     *
     * @throws InsufficientStockException If the source warehouse does not have enough stock.
     */
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

    /**
     * Complete a stock transfer by deducting the transferred quantity from the source warehouse
     * and adding it to the destination warehouse.
     *
     * @param StockTransfer $transfer The stock transfer record.
     *
     * @return bool Whether the operation was successful.
     */
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
            $transfer->status = StockTransferStatus::Completed;
            $transfer->transferred_at = now();
            $transfer->save();

            return true;
        });
    }

    /**
     * Cancel a pending stock transfer by releasing the reserved stock back to the source warehouse.
     *
     * @param StockTransfer $transfer The stock transfer record.
     *
     * @return bool Whether the operation was successful.
     */
    public function cancelTransfer(StockTransfer $transfer): bool
    {
        return DB::transaction(function () use ($transfer) {
            if ($transfer->status !== StockTransferStatus::Pending) {
                return false;
            }

            // Release reserved stock
            $sourceStock = Stock::getStock(
                $transfer->from_warehouse_id,
                $transfer->inventory_item_id
            );

            $sourceStock->release($transfer->quantity);

            // Update transfer status
            $transfer->status = StockTransferStatus::Cancelled;
            $transfer->save();

            return true;
        });
    }
}
