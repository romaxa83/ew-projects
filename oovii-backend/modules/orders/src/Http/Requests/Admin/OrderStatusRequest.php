<?php

namespace WezomCms\Orders\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class OrderStatusRequest extends FormRequest
{
    use LocalizedRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->localizeRules(
            [
                'name' => 'required|string|max:255',
                'notification_text' => 'nullable|string|max:2000',
            ],
            [
                'color' => 'required|string|regex:/#[0-9a-fA-F]{6}/',
                'amocrm_value_id' => 'nullable|string|max:255',
            ]
        );
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return $this->localizeAttributes(
            [
                'name' => __('cms-orders::admin.statuses.Name'),
                'notification_text' => __('cms-orders::admin.statuses.Notification text'),
            ],
            [
                'color' => __('cms-orders::admin.statuses.Color'),
                'amocrm_value_id' => __('cms-branches::admin.Amo value id'),
            ]
        );
    }
}
