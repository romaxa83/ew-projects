<?php

namespace App\GraphQL\Types\Commercial;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialSettings;
use GraphQL\Type\Definition\Type;

class CommercialSettingsType extends BaseType
{
    public const NAME = 'CommercialSettingsType';
    public const MODEL = CommercialSettings::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'nextcloud_link' => [
                'type' => Type::string(),
            ],
            'pdf' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'resolve' => fn(CommercialSettings $c) => $c->getFirstMedia($c::MEDIA_PDF),
            ],
            'rdp' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'resolve' => fn(CommercialSettings $c) => $c->getFirstMedia($c::MEDIA_RDP),
            ],
            'quote_title' => [
                'type' => Type::string(),
            ],
            'quote_address_line_1' => [
                'type' => Type::string(),
            ],
            'quote_address_line_2' => [
                'type' => Type::string(),
            ],
            'quote_phone' => [
                'type' => Type::string(),
            ],
            'quote_email' => [
                'type' => Type::string(),
            ],
            'quote_site' => [
                'type' => Type::string(),
            ],
        ];
    }
}
