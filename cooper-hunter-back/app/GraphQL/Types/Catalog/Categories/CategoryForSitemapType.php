<?php

namespace App\GraphQL\Types\Catalog\Categories;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use Illuminate\Support\Carbon;

class CategoryForSitemapType extends BaseType
{
    public const NAME = 'CategoryForSitemapType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'updated_at' => [
                'type' => NonNullType::int(),
                'description' => 'timestamp',
                'resolve' => static fn($a) => Carbon::createFromFormat(
                    DatetimeEnum::DEFAULT_FORMAT,
                    $a->updated_at
                )->timestamp,
            ],
        ];
    }
}
