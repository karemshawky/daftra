<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Warehouse;
use App\Filters\WarehouseStockFilter;
use App\Http\Resources\StockResource;
use App\Http\Resources\WarehouseResource;;
use App\Http\Requests\WarehouseInventoryRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WarehouseController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $warehouses = Warehouse::active()
            ->paginate()->withQueryString();

        return WarehouseResource::collection($warehouses);
    }

    public function show(Warehouse $warehouse): WarehouseResource
    {
        return WarehouseResource::make($warehouse);
    }

    public function inventory(Warehouse $warehouse, WarehouseStockFilter $filters, WarehouseInventoryRequest $request): AnonymousResourceCollection
    {
        $inventory = Stock::getWarehouseInventory($warehouse, $filters, $request);

        return StockResource::collection($inventory);
    }
}
