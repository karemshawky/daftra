<?php

namespace App\Http\Controllers;

use App\Filters\Warehouse\StockFilter;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Http\Resources\StockResource;
use App\Http\Resources\WarehouseResource;;

use App\Http\Requests\WarehouseInventoryRequest;

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

    public function inventory(Warehouse $warehouse, StockFilter $filters, WarehouseInventoryRequest $request)
    // public function inventory(WarehouseInventoryRequest $request, Warehouse $warehouse)
    {
        $inventory = Stock::getWarehouseInventory($warehouse, $filters, $request);
        // $inventory = Stock::getWarehouseInventory($warehouse, $request->validated());

        return StockResource::collection($inventory);
    }
}
