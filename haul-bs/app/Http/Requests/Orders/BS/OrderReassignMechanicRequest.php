<?php

namespace App\Http\Requests\Orders\BS;

use App\Models\Users\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="OrderReassignMechanicRequest",
 *     required={"mechanic_id"},
 *     @OA\Property(property="mechanic_id", type="integer", example="13"),
 * )
 */

class OrderReassignMechanicRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mechanic_id' => ['required', 'integer',
                Rule::exists(User::TABLE, 'id'),
                function ($attribute, $value, $fail) {
                    /** @var $user User */
                    $user = User::find($value);
                    if (!$user || !$user->role->isMechanic()) {
                        $fail(__('validation.custom.user.role.mechanic_not_found'));
                    }
                }
            ],
        ];
    }
}

