<?php

namespace App\Infrastructure\Shared\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use InvalidArgumentException;

abstract class BaseModel extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Auto-generate UUIDs
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Each model must define allowed filters.
     */
    abstract public static function filters(): array;

    /**
     * Apply filters + sorting to a query.
     */
    public static function applyFilters(array $filters): Builder
    {
        $instance = new static;
        $query = $instance->newQuery();
        $map = $instance->filters();

        foreach ($filters as $field => $value) {
            if (
                $value === null || $value === '' ||
                in_array($field, ['sort_by', 'sort_direction', 'per_page', 'order'])
            ) {
                continue;
            }

            if (!isset($map[$field])) {
                throw new InvalidArgumentException("Unsupported filter: {$field}");
            }

            $definition = $map[$field];
            [$operator, $column] = str_contains($definition, '.')
                ? explode('.', $definition, 2)
                : [$definition, $field];

            $query = self::applyOperator($query, $operator, $column, $value);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['order'] ?? $filters['sort_direction'] ?? 'desc';

        $query->orderBy($sortBy, $sortDirection);

        return $query;
    }


    protected static function applyOperator(Builder $query, string $operator, string $column, mixed $value): Builder
    {
        return match ($operator) {
            'equals' => $query->where($column, '=', $value),
            'like'   => $query->where($column, 'LIKE', "%{$value}%"),
            'after'  => $query->where($column, '>=', $value),
            'before' => $query->where($column, '<=', $value),
            'date'   => $query->whereDate($column, '=', $value),
            'isnull'  => $query->whereNull($column),
            'notnull' => $query->whereNotNull($column),
            default  => throw new InvalidArgumentException("Unsupported operator: {$operator}"),
        };
    }
}
