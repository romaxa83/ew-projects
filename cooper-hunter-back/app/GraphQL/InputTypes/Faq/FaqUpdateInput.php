<?php

namespace App\GraphQL\InputTypes\Faq;

use App\GraphQL\Types\NonNullType;

class FaqUpdateInput extends FaqCreateInput
{
    public const NAME = 'FaqUpdateInput';

    public function fields(): array
    {
        return [
                'id' => [
                    'type' => NonNullType::id(),
                ],
            ] + parent::fields();
    }
}
