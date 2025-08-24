<?php

namespace App\Filters;

use App\Filters\AbstractBaseFilter;

class StockFilter extends AbstractBaseFilter
{
    public function warehouse_id($search)
    {
        return $this->builder->where('warehouse_id', $search);
    }

    public function low_stock($search)
    {
        if ($search) {
            return $this->builder->whereHas('inventoryItem', function ($q) {
                $q->whereColumn('stocks.quantity', '<=', 'inventory_items.min_stock_level');
            });
        }
        return $this->builder;
    }
}
