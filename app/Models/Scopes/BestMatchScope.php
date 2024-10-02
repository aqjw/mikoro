<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BestMatchScope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, string $search, array $searchColumns, array $columns = ['*']): void
    {
        if (empty($search)) {
            return;
        }

        /**
        |--------------------------------------------------------------------------
        | Where statement
        |--------------------------------------------------------------------------
         */
        $builder->where(function ($query) use ($search, $searchColumns) {
            foreach ($searchColumns as $column) {
                $query->orWhere($column, 'like', "%{$search}%");
            }
        });

        /**
        |--------------------------------------------------------------------------
        | Select statement
        |--------------------------------------------------------------------------
         */
        $score_column_names = [];
        $score_columns = [];
        $bindings = [];

        foreach ($searchColumns as $column) {
            $score_column_names[] = "{$column}_score";
            $score_columns[] = "(
                        CASE WHEN {$column} = ? THEN 0
                            WHEN {$column} LIKE ? THEN 1
                            WHEN {$column} LIKE ? THEN 2
                            WHEN {$column} LIKE ? THEN 3
                        ELSE 4 END
                    ) AS '{$column}_score'";

            $bindings[] = $search;
            $bindings[] = "$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search";
        }

        $columns = is_string($columns) ? $columns : implode(',', $columns);
        $score_columns = implode(',', $score_columns);

        $builder->selectRaw("{$columns},{$score_columns}", $bindings);

        /**
        |--------------------------------------------------------------------------
        | Order statement
        |--------------------------------------------------------------------------
         */
        $builder->orderByRaw('(' . implode('+', $score_column_names) . ')');
    }
}
