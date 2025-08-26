<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Filters\StockFilter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Dedoc\Scramble\Attributes\Group;
use App\Http\Resources\StockResource;
use App\Http\Requests\ListStockRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group(name: 'Stock', weight: 4)]
class StockController extends Controller
{
    /**
     * List stocks.
     *
     * Returns a paginated list of stock with their warehouse, inventory item, and quantity.
     *
     * @param ListStockRequest $request
     * @param StockFilter $filters
     * @return AnonymousResourceCollection
     */
    public function index(ListStockRequest $request, StockFilter $filters): AnonymousResourceCollection
    {
        $stocks = Stock::listStocks($filters, $request);

        return StockResource::collection($stocks);
    }

    /**
     * Update stock.
     *
     * Update the specified stock in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stock  $stock
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Stock $stock): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $stock->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'data' =>  StockResource::make($stock->load(['warehouse', 'inventoryItem'])),
            'message' => 'Stock updated successfully',
        ], JsonResponse::HTTP_OK);
    }
}
