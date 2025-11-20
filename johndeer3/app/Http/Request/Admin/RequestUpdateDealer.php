<?php

namespace App\Http\Request\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Request Update Dealer",
 *     @OA\Property(property="user_ids", title="User ids", example="[1, 5]",
 *         description="Массив ID пользователей , если нужно удалить - присылаем или null или пустой массив"
 *     )
 * )
 */
class RequestUpdateDealer extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ];
    }
}
