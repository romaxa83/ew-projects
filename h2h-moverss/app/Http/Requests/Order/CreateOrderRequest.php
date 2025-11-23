<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\JsonRequest;
use App\Models\Settings\WaypointFlights;

class CreateOrderRequest extends JsonRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $waypointFlights = [0];
        if (WaypointFlights::get('id')->isNotEmpty()) {
            $waypointFlights = WaypointFlights::get('id')->pluck('id')->toArray();
        }


        return [
            'move-type' => 'required|in:local,interstate,intrastate',
            'move_size_id' => 'nullable|numeric|exists:move_sizes,id',
            'type' => 'nullable|in:house,apartment,storage,business', // TODO: On next add item -> move to cfg
            'source' => 'nullable|integer|exists:orders_sources,id',
            'work.date' => 'nullable|date',
            'work.types' => 'required|array',
            'work.types.*' => 'required|exists:works_types,id',
            'pickup.zip' => 'nullable|numeric|gt:0|max:99999',
            'pickup.address' => 'nullable|string|max:150',
            'pickup.stairs' => 'nullable|integer|in:0,' . implode(',', $waypointFlights),
            'pickup.elevator' => 'nullable|integer',
            'destination.zip' => 'nullable|numeric|gt:0|max:99999',
            'destination.address' => 'nullable|string|max:150',
            'destination.stairs' => 'nullable|integer|in:0,' . implode(',', $waypointFlights),
            'destination.elevator' => 'nullable|integer',
            'client.id' => 'nullable|exists:clients,id',
            'client.name' => 'nullable|string|max:50',
            'client.lname' => 'nullable|string|max:80',
            'client.phone' => 'nullable|string|max:20',
            'client.email' => 'nullable|email',
        ];
    }

    public function attributes(): array
    {
        return [
            'pickup.zip' => 'Pickup Zip',
            'destination.zip' => 'Destination Zip',
            'work.types' => 'Service Types',
        ];
    }

    protected function prepareForValidation()
    {
        $this->sanitizeInput();

        return $this->getValidatorInstance();
    }

    private function sanitizeInput(): void
    {
        $input = $this->all();

        if (!empty($input['client']['phone'])) {
            $input['client']['phone'] = preg_replace('/[^0-9]/', '', $input['client']['phone']);
        }

        $this->replace($input);
    }

}
