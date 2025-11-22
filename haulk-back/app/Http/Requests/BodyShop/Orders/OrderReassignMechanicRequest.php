<?php

namespace App\Http\Requests\BodyShop\Orders;

use App\Models\Users\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderReassignMechanicRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mechanic_id' => [
                'required',
                'integer',
                Rule::exists(User::TABLE_NAME, 'id'),
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if (!$user || $user->getRoleName() !== User::BSMECHANIC_ROLE) {
                        $fail(trans('Mechanic not found.'));
                    }
                }
            ],
        ];
    }
}
