<?php


namespace App\GraphQL\Types\Clients;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Managers\ManagerType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\PhoneType;
use App\Models\Clients\Client;
use GraphQL\Type\Definition\Type;

class ClientType extends BaseType
{
    public const NAME = 'ClientType';
    public const MODEL = Client::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'name' => [
                    'type' => NonNullType::string(),
                ],
                'contact_person' => [
                    'type' => NonNullType::string(),
                ],
                'edrpou' => [
                    'type' => Type::string(),
                ],
                'inn' => [
                    'type' => Type::string(),
                ],
                'manager' => [
                    'type' => ManagerType::type(),
                    'is_relation' => true,
                ],
                'phone' => [
                    'type' => NonNullType::string(),
                    'description' => 'Default phone',
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => fn(Client $client) => $client->phone->phone,
                ],
                'phones' => [
                    'type' => PhoneType::nonNullList(),
                    'description' => 'All phones list including default',
                    'is_relation' => true,
                ],
                'ban' => [
                    'type' => ClientBanType::type(),
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => fn(Client $client) => $client->ban_reason ? [
                        'reason' => $client->ban_reason,
                        'reason_description' => $client->ban_reason->description,
                        'show_in_inspection' => $client->show_ban_in_inspection,
                    ] : null
                ],
                'is_moderated' => [
                    'type' => NonNullType::boolean(),
                ],
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
            ]
        );
    }
}
