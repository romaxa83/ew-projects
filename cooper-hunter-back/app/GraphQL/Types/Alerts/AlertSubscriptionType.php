<?php

namespace App\GraphQL\Types\Alerts;


use App\GraphQL\Types\NonNullType;

class AlertSubscriptionType extends BaseAlertType
{

    public const NAME = 'AlertSubscriptionType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Id of alert'
            ]
        ];
    }
}
