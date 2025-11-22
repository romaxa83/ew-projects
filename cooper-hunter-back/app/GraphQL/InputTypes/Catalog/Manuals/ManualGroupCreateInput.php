<?php

namespace App\GraphQL\InputTypes\Catalog\Manuals;

use App\GraphQL\InputTypes\SimpleTranslationInput;
use App\GraphQL\Types\BaseInputType;
use GraphQL\Type\Definition\Type;

class ManualGroupCreateInput extends BaseInputType
{
    public const NAME = 'ManualGroupCreateInput';

    public function fields(): array
    {
        return [
            'show_commercial_certified' => [
                'type' => Type::boolean(),
            ],
            'translations' => [
                'type' => SimpleTranslationInput::nonNullList(),
            ],
        ];
    }
}
