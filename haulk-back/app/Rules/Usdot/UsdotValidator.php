<?php

namespace App\Rules\Usdot;

use App\Services\Usdot\UsdotService;
use Illuminate\Contracts\Validation\Rule;
use Throwable;

class UsdotValidator implements Rule
{
    private UsdotService $usdotService;

    public function __construct()
    {
        $this->usdotService = app(UsdotService::class);
    }

    public function passes($attribute, $value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        try {
            return (bool)$this->usdotService->getCarrierInfoByUsdot($value);
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function message(): string
    {
        return __('validation.attributes.usdot-not-exists');
    }
}
