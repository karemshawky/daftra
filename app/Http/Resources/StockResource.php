<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'warehouse_id' => $this->warehouse_id,
            // 'inventory_item_id' => $this->inventory_item_id,
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'item' => new InventoryItemResource($this->whenLoaded('inventoryItem')),
            'quantity' => $this->quantity,
            'reserved_quantity' => $this->reserved_quantity,
            'available_quantity' => $this->available_quantity,
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
