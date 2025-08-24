<?php

namespace App\Filters\InventoryItem;

use App\Filters\AbstractBaseFilter;

class InventoryItemFilter extends AbstractBaseFilter
{
    public function search($search)
    {
        return $this->builder->where(function ($q) use ($search) {
            $q->whereAny(['name', 'sku', 'description'], 'like', "%{$search}%");
        });
    }

    public function category($search)
    {
        return $this->builder->where('category_id', $search);
    }

    public function min_price($search)
    {
        return $this->builder->where('price', '>=', $search);
    }

    public function max_price($search)
    {
        return $this->builder->where('price', '<=', $search);
    }

    public function is_active($search)
    {
        return $this->builder->where('is_active', $search);
    }
}
