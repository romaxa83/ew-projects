<?php

namespace App\GraphQL\Types\Commercial\Commissioning;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\ProtocolTypeEnumType;
use App\Models\Commercial\Commissioning\Protocol;
use Core\Traits\Auth\AuthGuardsTrait;

class ProtocolType extends BaseType
{
    use AuthGuardsTrait;

    public const NAME = 'CommissioningProtocolType';
    public const MODEL = Protocol::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'type' => [
                    'type' => ProtocolTypeEnumType::nonNullType(),
                ],
                'translation' => [
                    'type' => ProtocolTranslationType::nonNullType(),
                ],
                'translations' => [
                    'type' => ProtocolTranslationType::nonNullList(),
                ],
                'questions' => [
                    'type' => QuestionType::list(),
                    'is_relation' => true,
                ],
            ],
        );
    }
}

