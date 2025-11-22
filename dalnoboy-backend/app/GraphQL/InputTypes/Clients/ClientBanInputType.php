<?php


namespace App\GraphQL\InputTypes\Clients;


use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Clients\BanReasonEnumType;
use App\GraphQL\Types\NonNullType;

class ClientBanInputType extends BaseInputType
{
    public const NAME = 'ClientBanInputType';

    public function fields(): array
    {
        return [
            'reason' => [
                'type' => BanReasonEnumType::nonNullType(),
            ],
            'show_in_inspection' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => false,
            ]
        ];
    }
}
