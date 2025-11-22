<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\Regulation;
use GraphQL\Type\Definition\Type;

class RegulationType extends BaseDictionaryType
{
    public const NAME = 'RegulationType';
    public const MODEL = Regulation::class;

    protected string $translateTypeClass = RegulationTranslateType::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'days' => [
                    'type' => Type::int(),
                ],
                'distance' => [
                    'type' => Type::int(),
                ],
            ]
        );
    }
}
