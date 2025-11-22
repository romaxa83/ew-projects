<?php

namespace App\GraphQL\InputTypes\News\Videos;

use App\GraphQL\Types\NonNullType;
use App\Models\News\Video;
use Illuminate\Validation\Rule;

class VideoUpdateInput extends VideoCreateInput
{
    public const NAME = 'VideoUpdateInput';

    public function fields(): array
    {
        return [
                'id' => [
                    'type' => NonNullType::id(),
                    'rules' => [Rule::exists(Video::TABLE, 'id')],
                ],
            ] + parent::fields();
    }
}
