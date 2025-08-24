<?php

namespace App\Filters\Warehouse;

use App\Filters\AbstractBaseFilter;

class StockFilter extends AbstractBaseFilter
{
    public function category_id($search)
    {
        return $this->builder->whereHas('inventoryItem', function ($q) use ($search) {
            $q->where('category_id', $search);
        });
    }

    public function min_price($search)
    {
        return $this->builder->whereHas('inventoryItem', function ($q) use ($search) {
            $q->where('price', '>=', $search);
        });
    }

    public function max_price($search)
    {
        return $this->builder->whereHas('inventoryItem', function ($q) use ($search) {
            $q->where('price', '<=', $search);
        });
    }
}
