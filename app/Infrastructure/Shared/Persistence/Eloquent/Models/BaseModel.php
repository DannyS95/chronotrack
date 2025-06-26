<?php

namespace App\Infrastructure\Shared\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

abstract class BaseModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Use UUIDs as primary keys.
     */
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

   public static function applyFilters(array $filters): Builder
    {
        $query = static::query();
        $map = static::filterMap();

        foreach ($map as $param => $rule) {
            $required = false;

            if (str_starts_with($rule, 'required.')) {
                $required = true;
                $rule = substr($rule, 9);
            }

            if (str_starts_with($rule, 'optional.')) {
                $rule = substr($rule, 9);
            }

            $hasValue = array_key_exists($param, $filters) && !is_null($filters[$param]);

            if (!$hasValue && $required) {
                throw new \InvalidArgumentException("Missing required filter: {$param}");
            }

            if (!$hasValue) {
                continue;
            }

            $value = $filters[$param];

            if ($rule === 'equals') {
                $query->where($param, '=', $value);
            } elseif (str_contains($rule, '.')) {
                [$operator, $column] = explode('.', $rule);
                $query->where($column, static::resolveOperator($operator), $value);
            }
        }

        return $query;
    }


    private static function resolveOperator(string $operator): string
    {
        return match ($operator) {
            'equals' => '=',
            'after' => '>=',
            'before' => '<=',
            default  => throw new \InvalidArgumentException("Unsupported operator: {$operator}"),
        };
    }
}
