<?php

namespace App\GraphQL\Types\Commercial;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Tax;
use Core\Traits\Auth\AuthGuardsTrait;

class TaxType extends BaseType
{
    use AuthGuardsTrait;

    public const NAME = 'TaxType';
    public const MODEL = Tax::class;

    public function fields(): array
    {
        return [
            'guid' => [
                'type' => NonNullType::string(),
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
            'value' => [
                'type' => NonNullType::float(),
            ],
        ];
    }
}

