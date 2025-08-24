<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Filters\StockFilter;
use Illuminate\Http\Request;
use App\Http\Resources\StockResource;
use App\Http\Requests\ListStockRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockController extends Controller
{
    public function index(ListStockRequest $request, StockFilter $filters): AnonymousResourceCollection
    {
        $stocks = Stock::listStocks($filters, $request);

        return StockResource::collection($stocks);
    }

    public function update(Request $request, Stock $stock): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $oldQuantity = $stock->quantity;

        $stock->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'data' =>  StockResource::make($stock->load(['warehouse', 'inventoryItem'])),
            'message' => 'Stock updated successfully',
            'changes' => [
                'previous_quantity' => $oldQuantity,
                'new_quantity' => $stock->quantity,
                'difference' => $stock->quantity - $oldQuantity
            ]
        ], JsonResponse::HTTP_OK);
    }
}
