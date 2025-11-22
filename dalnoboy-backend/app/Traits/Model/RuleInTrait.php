<?php


namespace App\Traits\Model;


use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

trait RuleInTrait
{
    public static function ruleExists(string $field = 'id'): Exists
    {
        return Rule::exists(static::class, $field);
    }
}
