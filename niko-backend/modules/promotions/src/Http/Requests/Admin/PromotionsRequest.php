<?php

namespace WezomCms\Promotions\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Http\Requests\ChangeStatus\RequiredIfMessageTrait;
use WezomCms\Core\Traits\LocalizedRequestTrait;
use WezomCms\Promotions\Models\Promotions;

class PromotionsRequest extends FormRequest
{
    use LocalizedRequestTrait;
    use RequiredIfMessageTrait;

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
                'text' => 'required|string',
                'link' => 'nullable|string|max:255',
            ],
            [
                'published' => 'nullable',
                'sort' => 'nullable|integer',
                'code_1c' => 'nullable|string|max:20|required_if:type,'. Promotions::TYPE_INDIVIDUAL .'|unique:promotions,code_1c,'.$this->promotion,
                'type' => 'required',
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
                'name' => __('cms-core::admin.layout.Name'),
                'text' => __('cms-promotions::admin.Text'),
                'link' => __('cms-promotions::admin.Link'),
            ],
            [
                'sort' => __('cms-core::admin.layout.Position'),
                'published' => __('cms-core::admin.layout.Published'),
                'link' => __('cms-promotions::admin.Link'),
                'code_1c' => __('cms-promotions::admin.Code 1c'),
                'type' => __('cms-promotions::admin.Type'),
            ]
        );
    }
}

