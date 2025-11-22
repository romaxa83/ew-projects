<?php

namespace App\GraphQL\Types\Alerts;


use App\GraphQL\Types\Enums\Alerts\AlertMemberTypeEnum;

class AlertMemberType extends BaseAlertType
{

    public const NAME = 'AlertMemberType';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'type' => [
                    'type' => AlertMemberTypeEnum::nonNullType(),
                ]
            ]
        );
    }
}
