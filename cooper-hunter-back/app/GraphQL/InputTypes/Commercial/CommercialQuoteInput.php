<?php

namespace App\GraphQL\InputTypes\Commercial;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;

class CommercialQuoteInput extends BaseInputType
{
    public const NAME = 'CommercialQuoteInput';

    public function fields(): array
    {
        return [
            'email' => [
                'type' => NonNullType::string(),
                'rules' => ['email:filter'],
            ],
            'shipping_address' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string'],
            ],
            'project_id' => [
                'type' => NonNullType::id(),
            ],
            'file' => [
                'type' => FileType::nonNullType(),
            ],
        ];
    }
}
