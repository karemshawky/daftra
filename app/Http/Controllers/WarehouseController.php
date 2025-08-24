<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Resources\StockResource;
use App\Http\Resources\WarehouseResource;;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::active()
            ->paginate()->withQueryString();

        return WarehouseResource::collection($warehouses);
    }

    public function show(Warehouse $warehouse)
    {
        return WarehouseResource::make($warehouse);
    }

    public function inventory(Request $request, Warehouse $warehouse)       ////////
    {
        $validated = $request->validate([
            'category' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $inventory = Stock::getWarehouseInventory($warehouse->id, $validated);

        return StockResource::collection($inventory);
    }
}
