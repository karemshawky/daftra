<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class AbstractBaseFilter
{
    protected $builder;

    public function __construct(?Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Apply the filters to the query builder.
     */
    public function apply(Builder $builder, object $request): Builder
    {
        $this->builder = $builder;

        foreach ($request->toArray() as $filter => $value) {
            if (method_exists($this, $filter) && !is_null($value)) {
                $this->$filter($value);
            }
        }

        return $this->builder;
    }
}
