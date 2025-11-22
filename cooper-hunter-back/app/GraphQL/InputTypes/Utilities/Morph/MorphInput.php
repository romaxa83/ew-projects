<?php

namespace App\GraphQL\InputTypes\Utilities\Morph;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Payments\PaymentCard\MorphSupportedTypeEnumType;
use App\GraphQL\Types\NonNullType;

class MorphInput extends BaseInputType
{
    public const NAME = 'MorphInput';

    public function fields(): array
    {
        return [
            'type' => [
                'type' => MorphSupportedTypeEnumType::Type(),
                'rules' => ['required', 'string']
            ],
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int']
            ],
        ];
    }
}
