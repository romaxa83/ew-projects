<?php

namespace WezomCms\Orders\Rules;

use Illuminate\Contracts\Validation\Rule;
use WezomCms\Orders\Services\SdekService;

class SdekCity implements Rule
{
    private SdekService $sdekService;

    /**
     * Create a new rule instance.
     * @param int|null $regionCode
     */
    public function __construct(private ?int $regionCode)
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

        $city = $this->sdekService->getCity($this->regionCode, (int) $value);

        return (bool) $city;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.in', [ 'attribute' => __('cms-orders::admin.courier.Locality') ]);
    }
}
