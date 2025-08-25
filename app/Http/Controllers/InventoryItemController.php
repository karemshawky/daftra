<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Filters\InventoryItemFilter;
use App\Http\Requests\FilterInventoryItem;
use App\Http\Resources\InventoryItemResource;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InventoryItemController extends Controller
{
    /**
     * Returns a paginated list of inventory items with their count of stocks.
     *
     * @param  \App\Filters\InventoryItemFilter  $filters
     * @param  \App\Http\Requests\FilterInventoryItem  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(InventoryItemFilter $filters, FilterInventoryItem $request): AnonymousResourceCollection
    {
        $inventoryItem = InventoryItem::filter($filters, $request);

        return InventoryItemResource::collection($inventoryItem);
    }

    /**
     * Stores a newly created inventory item in storage.
     *
     * @param  \App\Http\Requests\StoreInventoryItemRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        InventoryItem::create($request->validated());

        return response()->json(['message' => 'Inventory item created successfully'], JsonResponse::HTTP_CREATED);
    }

    /**
     * Display the specified inventory item.
     *
     * @param  \App\Models\InventoryItem  $inventoryItem
     * @return \App\Http\Resources\InventoryItemResource
     */
    public function show(InventoryItem $inventoryItem): InventoryItemResource
    {
        return InventoryItemResource::make($inventoryItem);
    }

    /**
     * Update the specified inventory item in storage.
     *
     * @param  \App\Http\Requests\UpdateInventoryItemRequest  $request
     * @param  \App\Models\InventoryItem  $inventoryItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): JsonResponse
    {
        $inventoryItem->update($request->validated());

        return response()->json(['message' => 'Inventory item updated successfully'], JsonResponse::HTTP_OK);
    }

    /**
     * Disable the specified inventory item.
     *
     * @param  \App\Models\InventoryItem  $inventoryItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventoryItem $inventoryItem): Response
    {
        $inventoryItem->update(['is_active' => false]);

        return response()->noContent();
    }
}
