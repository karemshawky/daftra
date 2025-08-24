<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use App\Http\Resources\StockResource;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'low_stock' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Stock::with(['warehouse', 'inventoryItem']);

        if (!empty($validated['warehouse_id'])) {
            $query->where('warehouse_id', $validated['warehouse_id']);
        }

        if (!empty($validated['low_stock'])) {
            $query->whereHas('inventoryItem', function ($q) {
                $q->whereColumn('stocks.quantity', '<=', 'inventory_items.min_stock_level');
            });
        }

        $stocks = $query->paginate($validated['per_page'] ?? 15);

        return StockResource::collection($stocks);
    }

    public function update(Request $request, Stock $stock)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $oldQuantity = $stock->quantity;
        $stock->quantity = $validated['quantity'];
        $stock->save();

        return response()->json([
            'data' => new StockResource($stock->load(['warehouse', 'inventoryItem'])),
            'message' => 'Stock updated successfully',
            'changes' => [
                'previous_quantity' => $oldQuantity,
                'new_quantity' => $stock->quantity,
                'difference' => $stock->quantity - $oldQuantity
            ]
        ]);
    }
}
