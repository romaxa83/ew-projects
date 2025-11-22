<?php


namespace App\GraphQL\InputTypes\Alert;


use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class AlertSendInputType extends BaseInputType
{
    public const NAME = 'AlertSendInputType';

    public function fields(): array
    {
        return [
            'recipients' => [
                'type' => AlertRecipientInputType::nonNullList()
            ],
            'title' => [
                'type' => NonNullType::string()
            ],
            'description' => [
                'type' => NonNullType::string()
            ]
        ];
    }
}
