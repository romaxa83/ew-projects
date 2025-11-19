<?php

declare(strict_types=1);

namespace Wezom\Quotes\Services\Calculation\Handlers;

use Wezom\Quotes\Services\Calculation\CalcPayload;
use Wezom\Quotes\Services\Calculation\CalculationHandler;

final class Total implements CalculationHandler
{
    public function handle(CalcPayload $payload): CalcPayload
    {
        $payload->total = $payload->cargoCost + $payload->storageCost + $payload->mileageRate;

        return $payload;
    }
}
