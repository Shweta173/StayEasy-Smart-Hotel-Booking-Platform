<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltersListings
{
    
    protected function applySearch(Builder $query, string $search, array $searchableFields): Builder
    {
        return $query->where(function ($q) use ($search, $searchableFields) {
            foreach ($searchableFields as $field) {
                if (str_contains($field, '.')) {
                    // Handle relationship fields
                    [$relation, $column] = explode('.', $field);
                    $q->orWhereHas($relation, function ($relQuery) use ($column, $search) {
                        $relQuery->where($column, 'like', "%{$search}%");
                    });
                } else {
                    // Handle direct fields
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            }
        });
    }
   
    protected function applyDateRange(
        Builder $query,
        string $field,
        ?string $startDate = null,
        ?string $endDate = null
    ): Builder {
        if ($startDate) {
            $query->where($field, '>=', $startDate);
        }

        if ($endDate) {
            $query->where($field, '<=', $endDate);
        }

        return $query;
    }
   
    protected function applyStatus(Builder $query, string $field, $status): Builder
    {
        if (is_array($status)) {
            return $query->whereIn($field, $status);
        }

        return $query->where($field, $status);
    }
   
    protected function applySorting(
        Builder $query,
        string $sortBy,
        string $sortOrder = 'asc',
        array $allowedFields = []
    ): Builder {
        // Validate sort field
        if (!empty($allowedFields) && !in_array($sortBy, $allowedFields)) {
            $sortBy = $allowedFields[0] ?? 'created_at';
        }

        // Validate sort order
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) 
            ? strtolower($sortOrder) 
            : 'asc';

        return $query->orderBy($sortBy, $sortOrder);
    }
   
    protected function applyPagination(Builder $query, int $perPage = 15, int $maxPerPage = 100)
    {
        // Limit per page to max
        $perPage = min($perPage, $maxPerPage);
        $perPage = max($perPage, 1); // At least 1

        return $query->paginate($perPage);
    }
   
    protected function applyCommonFilters(Builder $query, array $filters): Builder
    {
        // Search
        if (!empty($filters['search']) && !empty($filters['searchable_fields'])) {
            $query = $this->applySearch($query, $filters['search'], $filters['searchable_fields']);
        }

        // Date range
        if (!empty($filters['date_field'])) {
            $query = $this->applyDateRange(
                $query,
                $filters['date_field'],
                $filters['start_date'] ?? null,
                $filters['end_date'] ?? null
            );
        }

        // Status
        if (!empty($filters['status']) && !empty($filters['status_field'])) {
            $query = $this->applyStatus($query, $filters['status_field'], $filters['status']);
        }

        // Sorting
        if (!empty($filters['sort_by'])) {
            $query = $this->applySorting(
                $query,
                $filters['sort_by'],
                $filters['sort_order'] ?? 'asc',
                $filters['allowed_sort_fields'] ?? []
            );
        }

        return $query;
    }
}
