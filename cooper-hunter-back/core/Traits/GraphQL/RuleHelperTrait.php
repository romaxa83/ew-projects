<?php


namespace Core\Traits\GraphQL;


use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

trait RuleHelperTrait
{

    protected function existNoDeleted(string $model): Exists
    {
        return Rule::exists($model, 'id')
            ->whereNull('deleted_at');
    }
}
