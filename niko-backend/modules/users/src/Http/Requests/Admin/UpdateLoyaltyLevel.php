<?php

namespace WezomCms\Users\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Rules\PhoneOrPhoneMask;

class UpdateLoyaltyLevel extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'count_auto' => 'required|integer',
            'sum_service' => 'required|integer',
            'discount_sto' => 'required|integer',
            'discount_spares' => 'required|integer',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'count_auto' => __('cms-users::admin.loyalty count auto'),
            'sum_service' => __('cms-users::admin.loyalty sum service'),
            'discount_sto' => __('cms-users::admin.loyalty discount_sto'),
            'discount_spares' => __('cms-users::admin.loyalty discount_spares'),
        ];
    }

}
