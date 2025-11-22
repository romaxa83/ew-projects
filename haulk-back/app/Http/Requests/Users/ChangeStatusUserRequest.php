<?php

namespace App\Http\Requests\Users;

use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusUserRequest extends FormRequest
{
    use OnlyValidateForm;

    protected function checkRoles(): bool
    {
        /**@var User $user*/
        $user = $this->route()->parameter('user');
        return $user->id !== $this->user()->id && $this->user()->can('roles ' . mb_strtolower($user->getRoleName()));
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->checkRoles() && $this->user()->can('users update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user' => [
                static function ($attribute, User $value, $fail) {
                    if ($value->isPending()) {
                        $fail(trans('Can\'t change status for Pending'));
                    }
                }
            ],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(
            [
                'user' => $this->route()->parameter('user')
            ]
        );
    }
}
