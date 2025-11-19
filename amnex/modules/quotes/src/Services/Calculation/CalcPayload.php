<?php

declare(strict_types=1);

namespace Wezom\Quotes\Services\Calculation;

use Wezom\Quotes\Models\Quote;
use Wezom\Settings\Models\Setting;

final class CalcPayload
{
    public Quote $model;
    public array $settings;
    public array $error = [];
    public ?float $mile = null;
    public ?string $mileageRateKey = null;
    public null|float|int $mileageRate = null;
    public null|float|int $cargoCost = null;
    public null|float|int $storageCost = null;
    public null|float|int $total = null;

    public function __construct(Quote $model)
    {
        $this->model = $model;

        $this->settings = Setting::query()
            ->whereIn('key', [
                Setting::KEY_RATE_0_20_MILES,
                Setting::KEY_RATE_20_40_MILES,
                Setting::KEY_RATE_40_60_MILES,
                Setting::KEY_RATE_60_80_MILES,
                Setting::KEY_RATE_80_100_MILES,
                Setting::KEY_FURTHER_MILES,
                Setting::KEY_FURTHER_RATE,
                Setting::KEY_PRICE_FOR_PALLET,
                Setting::KEY_PRICE_FOR_PIECE,
                Setting::KEY_PRICE_FOR_STORAGE,
            ])
            ->get()
            ->pluck('value', 'key')
            ->toArray()
        ;
    }
}
