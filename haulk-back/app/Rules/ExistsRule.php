<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class ExistsRule implements Rule
{
    private Builder $query;
    private string $modelName;
    private string $field;

    public function __construct(string $model, string $field = 'id')
    {
        $this->modelName = $model;
        $this->query = resolve($model)->newQuery();
        $this->field = $field;
    }

    public function passes($attribute, $value): bool
    {
        $query = $this->query->clone();
        if (!is_array($value)) {
            return $query->where($this->field, $value)->exists();
        }
        $value = array_values(array_unique($value));
        $result = $query->whereIn($this->field, $value)->select('id')->get()->pluck('id');
        return $result->count() === count($value);
    }

    public function message(): string
    {
        return "No query results for model [{$this->modelName}]";
    }
}
