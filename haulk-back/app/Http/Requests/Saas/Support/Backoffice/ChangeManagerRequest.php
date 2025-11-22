<?php

namespace App\Http\Requests\Saas\Support\Backoffice;

use App\Permissions\Saas\Support\SupportRequestChangeManager;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class ChangeManagerRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can(SupportRequestChangeManager::KEY);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'manager_id' => [
                'nullable',
                'integer',
                'exists:exists:admins,id'
            ]
        ];
    }
}
