<?php

namespace App\Http\Request\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Attach Egs User Request",
 *     @OA\Property(property="eg_ids", description="Массив ID equipment group, (обязательно если выбрана роль pss)", example="[5, 44]"),
 *     required={"eg_ids"}
 * )
 */

class AttachEgsUserRequest extends FormRequest
{
    protected $userUpdate;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rule = [
            'eg_ids' => ['array'],
            'eg_ids.*' => ['exists:jd_equipment_groups,id'],
        ];

        return $rule;
    }
}
