<?php

namespace App\GraphQL\Types\Commercial\Commissioning;

use App\GraphQL\Types\BaseType;
use App\Models\Commercial\Commissioning\OptionAnswer;
use Core\Traits\Auth\AuthGuardsTrait;

class OptionAnswerType extends BaseType
{
    use AuthGuardsTrait;

    public const NAME = 'CommissioningOptionAnswerType';
    public const MODEL = OptionAnswer::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'translation' => [
                    'type' => OptionAnswerTranslationType::nonNullType(),
                ],
                'translations' => [
                    'type' => OptionAnswerTranslationType::nonNullList(),
                ],
            ],
        );
    }
}


