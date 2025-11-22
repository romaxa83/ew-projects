<?php

namespace App\GraphQL\Types\Catalog\Certificates;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Certificates\Certificate;
use GraphQL\Type\Definition\Type;

class CertificateType extends BaseType
{
    public const NAME = 'CertificateType';
    public const MODEL = Certificate::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'type_name' => [
                'type' => NonNullType::string(),
                'selectable' => false,
                'always' => 'certificate_type_id',
                'resolve' => static fn(Certificate $c): string => $c->type_name ?: $c->type->type,
            ],
            'number' => [
                'type' => NonNullType::string(),
            ],
            'link' => [
                'type' => Type::string(),
            ],
        ];
    }
}
