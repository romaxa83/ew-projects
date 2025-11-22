<?php

namespace App\GraphQL\InputTypes\Catalog\Products;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class CertificateInputType extends BaseInputType
{
    public const NAME = 'CertificateInput';

    public function fields(): array
    {
        return [
            'type_name' => [
                'type' => NonNullType::string(),
            ],
            'number' => [
                'type' => NonNullType::string(),
            ],
            'link' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string', 'url'],
            ],
        ];
    }
}
