<?php

use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\StockTransferController;

Route::name('api.')->group(function () {

    # Login
    Route::post('/login', [Auth\LoginController::class, 'login']);
    Route::get('/me', [Auth\LoginController::class, 'me'])->middleware('auth:sanctum');

    Route::middleware(['auth:sanctum'])->group(function () {
        // Warehouses
        Route::get('/warehouses', [WarehouseController::class, 'index']);
        Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show']);
        Route::get('/warehouses/{warehouse}/inventory', [WarehouseController::class, 'inventory']);

        // Inventory Items
        Route::apiResource('inventory-items', InventoryItemController::class);

        // Stock
        Route::get('/stocks', [StockController::class, 'index']);
        Route::patch('/stocks/{stock}', [StockController::class, 'update']);

        // Stock Transfers
        Route::get('/stock-transfers', [StockTransferController::class, 'index']);
        Route::post('/stock-transfers', [StockTransferController::class, 'store']);
        Route::get('/stock-transfers/{transfer}', [StockTransferController::class, 'show']);
        Route::post('/stock-transfers/{transfer}/complete', [StockTransferController::class, 'complete']);
        Route::post('/stock-transfers/{transfer}/cancel', [StockTransferController::class, 'cancel']);

        // Global inventory search
        // Route::get('/inventory', [InventoryItemController::class, 'index']);
    });
});
