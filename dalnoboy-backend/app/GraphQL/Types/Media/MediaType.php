<?php

namespace App\GraphQL\Types\Media;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Media\Media;
use Illuminate\Support\Collection;

class MediaType extends BaseType
{
    public const NAME = 'MediaType';
    public const MODEL = Media::class;

    protected const ALWAYS = [
        'id',
        'model_type',
        'model_id',
        'uuid',
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'conversions_disk',
        'size',
        'manipulations',
        'custom_properties',
        'generated_conversions',
        'responsive_images',
        'order_column',
        'created_at',
        'updated_at',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'always' => self::ALWAYS,
            ],
            'url' => [
                'type' => NonNullType::string(),
                'resolve' => static fn(Media $m) => $m->getUrl(),
                'selectable' => false,
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
            'file_name' => [
                'type' => NonNullType::string(),
            ],
            'size' => [
                'type' => NonNullType::string(),
            ],
            'mime_type' => [
                'type' => NonNullType::string(),
            ],
            'conventions' => [
                /** @see MediaType::resolveConventionsField() */
                'type' => MediaConversionType::list(),
                'selectable' => false,
            ]
        ];
    }

    protected function resolveConventionsField(Media $m): Collection
    {
        $conventions = collect();

        foreach ($m->getGeneratedConversions() as $convention => $isGenerated) {
            if ($isGenerated) {
                $conventions->push(
                    [
                        'convention' => $convention,
                        'url' => $m->getUrl($convention)
                    ]
                );
            }
        }

        return $conventions;
    }
}
