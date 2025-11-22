<?php

namespace App\GraphQL\Types\Catalog\Certificates;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Certificates\CertificateType as CertificateTypeModel;

class CertificateTypeType extends BaseType
{
    public const NAME = 'CertificateTypeType';
    public const MODEL = CertificateTypeModel::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'type' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}

