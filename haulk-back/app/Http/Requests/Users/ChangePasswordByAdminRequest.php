<?php


namespace App\Http\Requests\Users;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordByAdminRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'min:8', 'max:32','regex:/^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*)[0-9a-zA-Z]{8,}$/'],
            'password_confirmation' => ['required','same:password','min:8','max:191']
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}