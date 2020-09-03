<?php

namespace App\Traits\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Paginate
{
    public function paginate(Builder $query, ?int $limit, ?string $sort)
    {
        if (is_null($limit) || !in_array($limit, $this->availableLimits)) {
            $limit = $this->limit;
        }

        if (is_null($sort)) {
            $sort = $this->sortColumn;
        }

        $sorts = explode(',', $sort);
        foreach ($sorts as $sortColumn) {
            $sortDirection = Str::startsWith($sortColumn, '-') ? 'desc' : 'asc';
            $sortColumn = ltrim($sortColumn, '-');
            if (in_array($sortColumn, $this->availableSorColumns)) {
                $query->orderBy($sortColumn, $sortDirection);
            }
        }

        return $query->paginate($limit);
    }
}
