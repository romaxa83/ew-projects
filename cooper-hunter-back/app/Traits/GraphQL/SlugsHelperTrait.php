<?php

namespace App\Traits\GraphQL;

use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

trait SlugsHelperTrait
{
    protected string $slugArgsKey = 'slug';
    protected string $slugsArgsKey = 'slugs';

    protected function getSlugArgs(): array
    {
        return [
            $this->slugArgsKey => [
                'type' => Type::string(),
            ],
        ];
    }

    protected function getSlugsArgs(): array
    {
        return [
            $this->slugsArgsKey => Type::listOf(
                Type::string()
            ),
        ];
    }

    protected function getSlugsRules(Rule $rule = null): array
    {
        $rules = ['nullable', 'string'];

        if ($rule) {
            $rules[] = $rule;
        }

        return [
            $this->slugsArgsKey => ['nullable', 'array'],
            $this->slugsArgsKey . '.*' => $rules,
        ];
    }
}
