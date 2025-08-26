<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Warehouse;
use Dedoc\Scramble\Attributes\Group;
use App\Filters\WarehouseStockFilter;
use App\Http\Resources\StockResource;
use App\Http\Resources\WarehouseResource;;
use App\Http\Requests\WarehouseInventoryRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group(name: 'Warehouses', weight: 2)]
class WarehouseController extends Controller
{
    /**
     * List warehouses.
     *
     * Returns a paginated list of active warehouses.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $warehouses = Warehouse::active()
            ->paginate()->withQueryString();

        return WarehouseResource::collection($warehouses);
    }

    /**
     * Show warehouse.
     *
     * Returns a single warehouse resource.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \App\Http\Resources\WarehouseResource
     */
    public function show(Warehouse $warehouse): WarehouseResource
    {
        return WarehouseResource::make($warehouse);
    }

    /**
     * Warehouse inventory.
     *
     * Returns a paginated list of stock in a given warehouse, including the name and
     * quantity of each item.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @param  \App\Filters\WarehouseStockFilter  $filters
     * @param  \App\Http\Requests\WarehouseInventoryRequest  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function inventory(Warehouse $warehouse, WarehouseStockFilter $filters, WarehouseInventoryRequest $request): AnonymousResourceCollection
    {
        $inventory = Stock::getWarehouseInventory($warehouse, $filters, $request);

        return StockResource::collection($inventory);
    }
}
