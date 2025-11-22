<?php

namespace App\GraphQL\Types\Commercial\Commissioning;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\ProtocolTranslation;
use GraphQL\Type\Definition\Type;

class ProtocolTranslationType extends BaseType
{
    public const NAME = 'CommissioningProtocolTranslationType';
    public const MODEL = ProtocolTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'description' => [
                'type' => Type::string(),
                'alias' => 'desc'
            ],
            'language' => [
                'type' => NonNullType::string(),
            ],
            'title' => [
                'type' => Type::string(),
            ],
        ];
    }
}

