<?php

declare(strict_types=1);

namespace Wezom\Quotes\Services\Calculation;

interface CalculationHandler
{
    public function handle(CalcPayload $payload): CalcPayload;
}
