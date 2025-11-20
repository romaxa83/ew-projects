<?php

namespace WezomCms\Core\Http\Requests\ChangeStatus;

trait RequiredIfMessageTrait
{
    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'required_if' => __('cms-core::admin.layout.The attribute field is required when'),
        ];
    }
}
