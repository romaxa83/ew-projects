<?php

namespace App\GraphQL\Types\Enums\Alerts;

use App\Contracts\Alerts\AlertEnum;
use App\Enums\Alerts\AlertDealerEnum;
use App\Enums\Alerts\AlertModelEnum;
use App\Enums\Alerts\AlertOrderEnum;
use App\Enums\Alerts\AlertSupportRequestEnum;
use App\Enums\Alerts\AlertSystemEnum;
use App\Enums\Alerts\AlertTechnicianEnum;
use App\Enums\Alerts\AlertUserEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class AlertAdminTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'AlertAdminTypeEnum';
    public const DESCRIPTION = '';

    private const ALERT_ENUMS = [
        AlertModelEnum::ORDER => AlertOrderEnum::class,
        AlertModelEnum::SUPPORT_REQUEST => AlertSupportRequestEnum::class,
        AlertModelEnum::TECHNICIAN => AlertTechnicianEnum::class,
        AlertModelEnum::USER => AlertUserEnum::class,
        AlertModelEnum::SYSTEM => AlertSystemEnum::class,
        AlertModelEnum::DEALER => AlertDealerEnum::class,
    ];

    public function attributes(): array
    {
        $attributes = [
            'values' => []
        ];

        /**@var AlertEnum $enum */
        foreach (self::ALERT_ENUMS as $type => $enum) {
            $values = $enum::getBackList();
            foreach ($values as $value) {
                $attributes['values'][$type . '_' . $value] = $type . '_' . $value;
            }
        }

        return array_merge(
            parent::attributes(),
            $attributes
        );
    }
}
