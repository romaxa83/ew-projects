<?php

declare(strict_types=1);

namespace Wezom\Quotes\Services\Calculation\Handlers;

use Wezom\Quotes\Services\Calculation\CalcPayload;
use Wezom\Quotes\Services\Calculation\CalculationHandler;
use Wezom\Settings\Models\Setting;

final class Mileage implements CalculationHandler
{
    public function handle(CalcPayload $payload): CalcPayload
    {
        $payload->mile = $payload->model->distance->distance_as_mile * 2;

        if ($payload->mile >= 0 && $payload->mile < 20) {
            $payload->mileageRateKey = Setting::KEY_RATE_0_20_MILES;
        }
        if ($payload->mile >= 20 && $payload->mile < 40) {
            $payload->mileageRateKey = Setting::KEY_RATE_20_40_MILES;
        }
        if ($payload->mile >= 40 && $payload->mile < 60) {
            $payload->mileageRateKey = Setting::KEY_RATE_40_60_MILES;
        }
        if ($payload->mile >= 60 && $payload->mile < 80) {
            $payload->mileageRateKey = Setting::KEY_RATE_60_80_MILES;
        }
        if ($payload->mile >= 80 && $payload->mile < 100) {
            $payload->mileageRateKey = Setting::KEY_RATE_80_100_MILES;
        }
        if ($payload->mile > 100) {
            $payload->mileageRateKey = Setting::KEY_FURTHER_RATE;
        }

        // если дистанция не больше 100 миль, рейт получаем из настроек
        if ($payload->mileageRateKey !== Setting::KEY_FURTHER_RATE) {

            if (isset($payload->settings[$payload->mileageRateKey])) {
                $payload->mileageRate = (float)$payload->settings[$payload->mileageRateKey];
            }

        } else {
            // если более 100, высчитываем рейт по формуле, на основе данных из настроек

            if (!isset($payload->settings[Setting::KEY_FURTHER_RATE])) {
                $payload->error = array_merge(['there is no value in the settings - ' . Setting::KEY_FURTHER_RATE], [$payload->error]);

                return $payload;
            }
            if (!isset($payload->settings[Setting::KEY_FURTHER_MILES])) {
                $payload->error = array_merge(['there is no value in the settings - ' . Setting::KEY_FURTHER_MILES], [$payload->error]);

                return $payload;
            }
            if (!isset($payload->settings[Setting::KEY_RATE_80_100_MILES])) {
                $payload->error = array_merge(['there is no value in the settings - ' . Setting::KEY_RATE_80_100_MILES], [$payload->error]);

                return $payload;
            }

            $lastFixRate = (float)$payload->settings[Setting::KEY_RATE_80_100_MILES];
            $furterIncrement = (int)$payload->settings[Setting::KEY_FURTHER_MILES];
            $furterRate = (float)$payload->settings[Setting::KEY_FURTHER_RATE];

            if ($furterIncrement == 0) {
                $payload->error = array_merge([Setting::KEY_FURTHER_MILES . ' can\'t be 0 '], [$payload->error]);

                return $payload;
            }

            $payload->mileageRate = round(
                $lastFixRate + $furterRate * ceil(($payload->mile - 100) / $furterIncrement),
                2
            );
        }

        return $payload;
    }
}
