<?php


namespace App\GraphQL\Types\Clients;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Clients\BanReasonEnumType;
use App\GraphQL\Types\NonNullType;

class ClientBanType extends BaseType
{
    public const NAME = 'ClientBanType';

    public function fields(): array
    {
        return [
            'reason' => [
                'type' => BanReasonEnumType::nonNullType()
            ],
            'reason_description' => [
                'type' => NonNullType::string()
            ],
            'show_in_inspection' => [
                'type' => NonNullType::boolean()
            ]
        ];
    }
}
