<?php

namespace App\Http\Requests\Orders;

use App\Rules\Orders\IsNotLastVehicle;
use App\Rules\Orders\VehicleInOrder;
use Illuminate\Foundation\Http\FormRequest;

class DeleteVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route()->parameter('order'));
    }

    public function prepareForValidation()
    {
        $this->merge([
            'vehicle' => $this->route()->parameter('vehicle'),
            'order' => $this->route()->parameter('order')
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'vehicle' => [new VehicleInOrder($this->order)],
            'order' => [new IsNotLastVehicle()]
        ];
    }
}
