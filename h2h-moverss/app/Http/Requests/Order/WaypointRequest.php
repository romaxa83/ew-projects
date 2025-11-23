<?php

namespace App\Http\Requests\Order;

use App\Models\Settings\WaypointFlights;
use Illuminate\Foundation\Http\FormRequest;

class WaypointRequest extends FormRequest
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
            'id' => 'nullable|integer|exists:orders_waypoints,id',
            'order_id' => 'required|integer|exists:orders,id',
            'type' => 'required|in:pickup,destination',
            'state' => 'required|string|max:2',
            'zip' => 'required|numeric|digits:5',
            'city' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:150',
            'ap' => 'nullable|string|max:20',
            'building_type_id' => 'required|exists:building_types,id',
//            'flights_id' => 'nullable|in:0,'.implode(',', array_keys(config('app.waypoints_flights'))),
            'flights_id' => 'nullable|in:0,' . implode(',', $waypointFlights),
            'parking_type_id' => 'required|exists:parking_types,id',
            'has_elevator' => 'nullable|boolean',
            'sort' => 'nullable|integer',
            'lat' => ['nullable', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'lng' => ['nullable', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'notes.*.id' => 'nullable|numeric',
            'notes.*.value' => 'required|string|max:65536',
            'miscs.usedAutocomplete' => 'boolean',
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

        $input['has_elevator'] = !empty($input['has_elevator']) ? 1 : 0;

        if (!empty($input['city'])) {
            $input['city'] = strip_tags($input['city']);
        }
        if (!empty($input['address'])) {
            $input['address'] = strip_tags($input['address']);
        }

        if (isset($input['notes'])) {
            $input['notes'] = $this->sanitizeNotes($input['notes'], $input['order_id']);
        }

        $this->replace($input);
    }

    private function sanitizeNotes($values, $order_id): array
    {
        return collect($values)
            ->map(function ($v) use ($order_id) {
                return [
                    'id' => $v['id'],
                    'order_id' => $order_id,
                    'value' => strip_tags($v['value']),
                ];
            })
            ->filter(function ($item) {
                return !empty($item['value']);
            })
            ->all();
    }

}
