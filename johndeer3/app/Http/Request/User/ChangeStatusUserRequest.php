<?php

namespace App\Http\Request\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Change Status User Request",
 *     @OA\Property(property="status", type="integer", description="0/1 (false/true)", example=1),
 *     required={"status"}
 * )
 */

class ChangeStatusUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:1,0'],
        ];
    }
}
