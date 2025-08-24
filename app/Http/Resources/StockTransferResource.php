<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
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
            'transfer_number' => $this->transfer_number,
            'from_warehouse' => new WarehouseResource($this->whenLoaded('fromWarehouse')),
            'to_warehouse' => new WarehouseResource($this->whenLoaded('toWarehouse')),
            'item' => new InventoryItemResource($this->whenLoaded('inventoryItem')),
            'quantity' => $this->quantity,
            'status_id' => $this->status,
            'status' => $this->status->asAString(),
            'notes' => $this->notes,
            'created_by' => $this->whenLoaded('createdBy', UserResource::make($this->createdBy)),
            'transferred_at' => $this->transferred_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
