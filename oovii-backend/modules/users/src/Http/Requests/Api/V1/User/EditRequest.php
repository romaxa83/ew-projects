<?php

namespace WezomCms\Users\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Edit a user"
 * )
 */
class EditRequest extends FormRequest
{
    public function rules(): array
    {
        $user = \Auth::user();

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'lang' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [];
    }

    /**
     * @OA\Property(property="name", title="Name", description="Имя пользователя", example="Иван")
     * @OA\Property(property="surname", title="Surname", description="Фамилия пользователя", example="Иванов")
     * @OA\Property(property="patronymic", title="Patrynomic", description="Очество пользователя", example="Иваныч")
     * @OA\Property(property="email", title="Email", description="Email", example="ivan@gmail.com")
     * @OA\Property(property="lang", title="Language", description="Локаль для пользователя", example="ru")
     */
}

