<?php

namespace App\GraphQL\Types\Catalog\Troubleshoots\Troubleshoots;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Troubleshoots\Groups;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Troubleshoots\Troubleshoot;

class TroubleshootType extends BaseType
{
    public const NAME = 'TroubleshootType';
    public const MODEL = Troubleshoot::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'id' => [
                    'type' => NonNullType::id(),
                ],
                'active' => [
                    'type' => NonNullType::boolean()
                ],
                'name' => [
                    'type' => NonNullType::string()
                ],
                'group' => [
                    'type' => Groups\TroubleshootGroupType::type(),
                    'is_relation' => true,
                ],
                'pdf' => [
                    'type' => MediaType::type(),
                    'alias' => 'media',
                    'always' => 'id',
                    'resolve' => static fn(Troubleshoot $m) => $m->getFirstMedia(Troubleshoot::MEDIA_COLLECTION_NAME)
                ],
            ]
        );
    }
}


