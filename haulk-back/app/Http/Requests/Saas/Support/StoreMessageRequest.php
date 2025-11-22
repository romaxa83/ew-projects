<?php

namespace App\Http\Requests\Saas\Support;

use App\Models\Admins\Admin;
use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $supportRequest = $this->route()->parameter('supportRequest');

        if ($this->user(User::GUARD)) {
            return $this->user()->can('reply', $supportRequest);
        }

        if ($this->user(Admin::GUARD)) {
            return $this->user()->can('answer', $supportRequest);
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'message' => [
                'required',
                'string'
            ],
            'attachments.*' => [
                'nullable',
                'file',
                'mimes:pdf,png,jpg,jpeg,jpe,doc,docx,txt,xls,xlsx'
            ]
        ];
    }
}
