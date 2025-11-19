<?php

declare(strict_types=1);

namespace Wezom\Quotes\Services\Calculation\Handlers;

use Wezom\Quotes\Services\Calculation\CalcPayload;
use Wezom\Quotes\Services\Calculation\CalculationHandler;
use Wezom\Settings\Models\Setting;

final class Storage implements CalculationHandler
{
    public function handle(CalcPayload $payload): CalcPayload
    {
        if(is_null($payload->model->is_palletized)){
            $payload->storageCost = 0;
            return $payload;
        }

        if (!isset($payload->settings[Setting::KEY_PRICE_FOR_STORAGE])) {
            $payload->error = array_merge(['there is no value in the settings - ' . Setting::KEY_PRICE_FOR_STORAGE], [$payload->error]);

            return $payload;
        }

        $payload->storageCost = round(
            (float)$payload->settings[Setting::KEY_PRICE_FOR_STORAGE] * $payload->model->days_stored,
            2
        );

        return $payload;
    }
}
