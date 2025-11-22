<?php

namespace App\GraphQL\Types\Alerts;


use App\GraphQL\Types\Enums\Alerts\AlertAdminTypeEnum;

class AlertAdminType extends BaseAlertType
{

    public const NAME = 'AlertAdminType';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'type' => [
                    'type' => AlertAdminTypeEnum::nonNullType()
                ]
            ]
        );
    }
}
