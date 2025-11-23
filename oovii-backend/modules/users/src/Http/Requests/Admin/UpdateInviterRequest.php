<?php

namespace WezomCms\Users\Http\Requests\Admin;

class UpdateInviterRequest extends CreateUserRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'bonus' => 'nullable|integer|min:0',
        ];
    }
}
