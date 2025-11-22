<?php

namespace App\GraphQL\Types\Faq;

use App\GraphQL\Types\BaseType;
use App\Models\Faq\Faq;
use GraphQL\Type\Definition\Type;

class FaqType extends BaseType
{
    public const NAME = 'FaqType';
    public const MODEL = Faq::class;

    public function fields(): array
    {
        return parent::fields() + [
                'active' => [
                    'type' => Type::boolean(),
                ],
                'sort' => [
                    'type' => Type::int(),
                ],
                'translation' => [
                    'type' => FaqTranslationType::nonNullType(),
                ],
                'translations' => [
                    'type' => FaqTranslationType::nonNullList()
                ],
            ];
    }
}
