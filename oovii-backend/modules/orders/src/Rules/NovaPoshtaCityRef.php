<?php

namespace WezomCms\Orders\Rules;

use Illuminate\Contracts\Validation\Rule;
use Lang;
use WezomCms\Orders\Contracts\NovaPoshtaServiceInterface;

class NovaPoshtaCityRef implements Rule
{
    /**
     * @var NovaPoshtaServiceInterface
     */
    protected $service;

    /**
     * NovaPoshtaCityRef constructor.
     */
    public function __construct()
    {
        $this->service = app(NovaPoshtaServiceInterface::class);
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
        return (bool) $this->service->getCity($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return Lang::get('cms-orders::' . app('side') . '.Invalid city');
    }

    /**
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString()
    {
        return "Nova Poshta city";
    }
}
