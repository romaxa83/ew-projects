<?php

namespace WezomCms\Users\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use WezomCms\Core\Rules\Phone;
use WezomCms\Users\Models\User;
use WezomCms\Users\Rules\EmailOrPhone;

class ChangeStatusFrom1CRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'AccountID' => ['required'],
            'AccountStatusID' => ['required'],
            'LoyaltyProgramTypeID' => ['nullable'],
            'LoyaltyLevelID' => ['nullable'],
            'LevelUpAmount' => ['nullable'],
            'PurchasedСars' => ['nullable'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }
}
