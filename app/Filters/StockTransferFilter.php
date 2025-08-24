<?php

namespace App\Filters;

use App\Filters\AbstractBaseFilter;

class StockTransferFilter extends AbstractBaseFilter
{
    public function status($search)
    {
        return $this->builder->where('status', $search);
    }

    public function from_warehouse_id($search)
    {
        return $this->builder->where('from_warehouse_id', $search);
    }

    public function to_warehouse_id($search)
    {
        return $this->builder->where('to_warehouse_id', $search);
    }
}
