<?php

namespace Core\Traits\GraphQL\Queries;

use App\Rules\SortParameterRule;
use GraphQL\Type\Definition\Type;

trait SortHelperTrait
{
    protected function sortArgs(?string $defaultValue = null): array
    {
        $sort = [
            'type' => Type::string(),
            'description' => 'Аргумент сортировки. Доступные поля: ' . $this->allowedForSortFieldsToString(),
        ];

        if ($defaultValue) {
            $sort['defaultValue'] = $defaultValue;
        }

        return compact('sort');
    }

    protected function allowedForSortFieldsToString(): string
    {
        return implode(', ', $this->allowedForSortFields());
    }

    protected function allowedForSortFields(): array
    {
        return [];
    }

    protected function sortRules(): array
    {
        return [
            'sort' => ['nullable', 'string', new SortParameterRule($this->allowedForSortFields())]
        ];
    }
}
