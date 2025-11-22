<?php

namespace App\Http\Requests\Orders\Parts;

use App\Models\Users\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="OrderAssignSalesManagerRequest",
 *     required={"sales_manager_id"},
 *     @OA\Property(property="sales_manager_id", type="integer", example="13"),
 * )
 */

class OrderAssignSalesManagerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sales_manager_id' => ['required', 'integer',
                Rule::exists(User::TABLE, 'id'),
                function ($attribute, $value, $fail) {
                    /** @var $user User */
                    $user = User::find($value);
                    if (!$user || !$user->role->isSalesManager()) {
                        $fail(__('validation.custom.user.role.sales_manager_not_found'));
                    }
                    if (!$user || !$user->status->isActive()) {
                        $fail(__('validation.custom.user.is_not_active'));
                    }
                }
            ],
        ];
    }
}
