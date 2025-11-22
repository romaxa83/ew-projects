<?php

namespace App\GraphQL\Types;

use Core\Traits\GraphQL\Types\BaseTypeTrait;
use GraphQL\Type\Definition\NullableType;
use Illuminate\Database\Eloquent\Model;
use Rebing\GraphQL\Support\Type;

abstract class BaseType extends Type implements NullableType
{
    use BaseTypeTrait;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'created_at' => [
                'type' => NonNullType::int(),
                'resolve' => static fn(Model $model) => $model->created_at->getTimestamp(),
                'description' => 'UNIX time',
            ],
            'updated_at' => [
                'type' => NonNullType::int(),
                'resolve' => static fn(Model $model) => $model->updated_at->getTimestamp(),
                'description' => 'UNIX time',
            ],
        ];
    }
}
