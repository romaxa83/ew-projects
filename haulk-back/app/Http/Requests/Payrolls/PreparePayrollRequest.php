<?php

namespace App\Http\Requests\Payrolls;

use App\Models\Orders\Order;
use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class PreparePayrollRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('payrolls create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'driver_id' => [
                'required',
                'integer',
                'exists:' . User::class . ',id'
            ],
            'orders' => [
                'required',
                'array'
            ],
            'orders.*.id' => [
                'required',
                'int',
                'exists:' . Order::class . ',id,deleted_at,NULL'
            ],
            'orders.*.load_id' => [
                'required',
                'string'
            ]
        ];
    }

    public function orders(): array
    {
        return array_map(
            fn(array $item) => $item['id'],
            $this->validated()['orders']
        );
    }

    public function driverId(): int
    {
        return $this->validated()['driver_id'];
    }
}
