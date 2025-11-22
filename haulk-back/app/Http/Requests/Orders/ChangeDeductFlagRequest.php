<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Order;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class ChangeDeductFlagRequest extends FormRequest
{

    use OnlyValidateForm;

    /**@var Order $order*/
    private $order;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->order = $this->route()->parameter('order');
        return $this->user()->can('orders deduct-from-driver');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        if ($this->order->deduct_from_driver === true) {
            return [];
        }
        return [
            'deducted_note' => [
                'nullable',
                'string',
                'min:2',
                'max:1000'
            ]
        ];
    }

    public function validated(): array
    {
        $validated = parent::validated();
        return [
            'deduct_from_driver' => !$this->order->deduct_from_driver,
            'deducted_note' => $this->order->deduct_from_driver ? null : data_get($validated, 'deducted_note')
        ];
    }
}
