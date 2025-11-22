<?php

namespace App\GraphQL\InputTypes\News;

use App\GraphQL\Types\NonNullType;
use App\Models\News\News;
use Illuminate\Validation\Rule;

class NewsUpdateInput extends NewsCreateInput
{
    public const NAME = 'NewsUpdateInput';

    public function fields(): array
    {
        return [
                'id' => [
                    'type' => NonNullType::id(),
                    'rules' => [Rule::exists(News::TABLE, 'id')],
                ],
            ] + parent::fields();
    }
}
