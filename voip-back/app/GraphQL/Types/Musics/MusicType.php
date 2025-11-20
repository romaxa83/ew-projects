<?php

namespace App\GraphQL\Types\Musics;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Departments\DepartmentType;
use App\Models\Musics\Music;
use GraphQL\Type\Definition\Type;
use App\GraphQL\Types\Media\MediaType;

class MusicType extends BaseType
{
    public const NAME = 'MusicType';
    public const MODEL = Music::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'interval' => [
                    'type' => Type::int(),
                ],
                'active' => [
                    'type' => Type::boolean(),
                ],
                'is_hold_state' => [
                    'type' => Type::boolean(),
                    'alias' => 'has_unhold_data'
                ],
                'department' => [
                    'type' => DepartmentType::nonNullType(),
                ],
                'record' => [
                    'type' => MediaType::type(),
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => static fn(Music $model) => $model->media->first()
                ],
            ]
        );
    }
}

