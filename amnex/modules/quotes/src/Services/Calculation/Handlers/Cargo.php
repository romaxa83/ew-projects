<?php

declare(strict_types=1);

namespace Wezom\Quotes\Services\Calculation\Handlers;

use Wezom\Quotes\Services\Calculation\CalcPayload;
use Wezom\Quotes\Services\Calculation\CalculationHandler;
use Wezom\Settings\Models\Setting;

final class Cargo implements CalculationHandler
{
    public function handle(CalcPayload $payload): CalcPayload
    {
        if(is_null($payload->model->is_palletized)){
            $payload->cargoCost = 0;
            return $payload;
        }

        if ($payload->model->is_palletized) {

            if (!isset($payload->settings[Setting::KEY_PRICE_FOR_PALLET])) {
                $payload->error = array_merge(['there is no value in the settings - ' . Setting::KEY_PRICE_FOR_PALLET], [$payload->error]);

                return $payload;
            }

            $payload->cargoCost = round(
                (float)$payload->settings[Setting::KEY_PRICE_FOR_PALLET] * $payload->model->number_pallets,
                2
            );
        } else {

            if (!isset($payload->settings[Setting::KEY_PRICE_FOR_PIECE])) {
                $payload->error = array_merge(['there is no value in the settings - ' . Setting::KEY_PRICE_FOR_PIECE], [$payload->error]);

                return $payload;
            }

            $payload->cargoCost = round(
                (float)$payload->settings[Setting::KEY_PRICE_FOR_PIECE] * $payload->model->piece_count,
                2
            );
        }

        return $payload;
    }
}
