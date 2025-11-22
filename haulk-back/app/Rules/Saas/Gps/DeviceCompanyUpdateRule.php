<?php

namespace App\Rules\Saas\Gps;

use App\Models\Saas\GPS\Device;
use Illuminate\Contracts\Validation\Rule;

class DeviceCompanyUpdateRule implements Rule
{
    private $id;

    /**
     * Create a new rule instance.
     * @param string $type
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        /** @var $model Device */
        $model = Device::query()->where('id', $this->id)->first();

        if(
            $model->company_id
            && $model->company_id != $value
            && $model->status->isActive()
        ){
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.custom.gps.device.cant_change_company_active_device');
    }
}

