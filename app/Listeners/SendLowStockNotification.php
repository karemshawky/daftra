<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLowStockNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LowStockDetected $event): void
    {
        $stock = $event->stock;

        // Log the low stock event
        Log::warning('Low stock detected', [
            'warehouse' => $stock->warehouse->name,
            'item' => $stock->inventoryItem->name,
            'sku' => $stock->inventoryItem->sku,
            'current_quantity' => $stock->quantity,
            'min_level' => $stock->inventoryItem->min_stock_level
        ]);

        // In production, send email notification
        // Mail::to(config('mail.admin_email'))->send(new LowStockAlert($stock));
    }
}
