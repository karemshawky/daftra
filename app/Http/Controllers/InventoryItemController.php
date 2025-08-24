<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterInventoryItem;
use App\Http\Resources\InventoryItemResource;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Filters\InventoryItem\InventoryItemFilter;

class InventoryItemController extends Controller
{
    public function index(InventoryItemFilter $filters, FilterInventoryItem $request)
    {
        $inventoryItem = InventoryItem::filter($filters, $request);
        
        $inventoryItem->loadCount('stocks');

        return InventoryItemResource::collection($inventoryItem);
    }

    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        InventoryItem::create($request->validated());

        return response()->json(['message' => 'Inventory item created successfully'], JsonResponse::HTTP_CREATED);
    }

    public function show(InventoryItem $inventoryItem): InventoryItemResource
    {
        $inventoryItem->loadCount('stocks');

        return InventoryItemResource::make($inventoryItem);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): JsonResponse
    {
        $inventoryItem->update($request->validated());

        return response()->json(['message' => 'Inventory item updated successfully'], JsonResponse::HTTP_OK);
    }

    public function destroy(InventoryItem $inventoryItem): Response
    {
        $inventoryItem->update('is_active', false);

        return response()->noContent();
    }
}
