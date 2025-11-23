<?php

namespace WezomCms\Orders\Rules;

use Illuminate\Contracts\Validation\Rule;
use WezomCms\Orders\Services\SdekService;

class SdekRegion implements Rule
{
    private SdekService $sdekService;

    /**
     * Create a new rule instance.
     *
     */
    public function __construct()
    {
        $this->sdekService = resolve(SdekService::class);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (isLocal() || app()->runningUnitTests()) {
            return true;
        }

        $region = $this->sdekService->getRegion((int) $value);

        return (bool) $region;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.in', [ 'attribute' => __('cms-orders::admin.courier.Region') ]);
    }
}
