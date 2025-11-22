<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteInspectionRequest extends FormRequest
{

    private $order;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->order = $this->route()->parameter('order');

        return $this->user()->can('allowEmptyInspection', $this->order);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'inspection_type' => [
                'required',
                'string',
                'in:' . Order::LOCATION_PICKUP . ',' . Order::LOCATION_DELIVERY
            ],
            'bol_file' => [
                Rule::requiredIf(
                    function () {
                        return $this->order->inspection_type === Order::INSPECTION_TYPE_NONE_W_FILE;
                    }
                ),
                'file',
                'mimes:pdf'
            ],
            'actual_date' => [
                'nullable',
                'integer'
            ]
        ];
    }
}
