<?php

namespace Tests\Helpers\Traits;

use App\Models\Settings\Setting;

trait SettingFactoryHelper
{
    protected function billingEmailFactory()
    {
        factory(Setting::class)->create(
            [
                'group' => Setting::GROUP_CARRIER,
                'alias' => 'billing_email',
                'value' => 'billing_email@example.com'
            ]
        );
    }
}
