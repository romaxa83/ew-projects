<?php

namespace App\Http\Requests\Orders;

use App\Models\Users\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int driver_id
 */
class TakeOfferRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'driver_id' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                $user = User::find($value);
                if (!$user || !$user->isDriver()) {
                    $fail(trans('Driver not found.'));
                }
            }],
        ];
    }
}
