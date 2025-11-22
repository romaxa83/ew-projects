<?php

namespace App\Http\Requests\Orders;

use App\Dto\Orders\InspectDamageDto;
use App\Models\Orders\Order;
use App\Models\Orders\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InspectDamageRequest extends FormRequest
{

    private Order $order;

    private Vehicle $vehicle;

    private function setRouteParams(): void
    {
        $order = $this->route()->parameter('order');
        $vehicle = $this->route()->parameter('vehicle');

        if ($order instanceof Order) {
            $this->order = $order;
        }

        if ($vehicle instanceof Vehicle) {
            $this->vehicle = $vehicle;
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->setRouteParams();
        return $this->user()->can('orders inspection')
            && $this->user()->can('viewAssignedToMe', $this->order)
            && $this->order->id === $this->vehicle->order_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'damage_labels' => [
                'nullable',
                'array'
            ],
            'damage_labels.*' => [
                'required',
                'string',
                Rule::in(array_keys(config('orders.inspection.damage_labels')))
            ],
            Order::INSPECTION_DAMAGE_FIELD_NAME => [
                'required',
                'file',
            ],
        ];
    }

    /**
     * @return InspectDamageDto
     */
    public function dto(): InspectDamageDto
    {
        return InspectDamageDto::byParams($this->validated());
    }
}
