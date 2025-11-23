<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use App\Models\Settings\EstimateParameters;
use Illuminate\Foundation\Http\FormRequest;

class EstimateSaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'order_id' => 'required|integer|exists:orders,id',
            'type' => 'required|in:'.implode(',', array_keys(config('app.moving_types'))),
            'is_locked' => 'nullable|boolean',
            'trucks' => 'nullable|integer|max:99',
            'crews' => 'nullable|integer|max:99',
            'discount_value' => 'nullable|numeric|max:9999',
            'discount_type' => 'nullable|in:null,sum,percent',
            'travel_fee' => 'nullable|numeric|max:9999',
            'fee_type' => 'nullable|in:sum'.($this->type === 'local' ? ',percent' : ''),
            'calculated_moving_distance_is_auto' => 'nullable|boolean',
            'calculated_moving_distance' => 'nullable|numeric|max:9999',
        ];

        if ($this->type === 'local') {
            $rules += [
                'local.hours_min' => 'numeric|max:999',
                'local.hours_max' => 'numeric|max:999', // |gte:local.hours_min
                'local.rate' => 'nullable|numeric|max:999',
                'local.is_auto' => 'nullable|boolean',
            ];
        } elseif ($this->type === 'intrastate') {
            $rules += [
                'intrastate.rate' => 'nullable|numeric|max:9999',
                'intrastate.is_auto' => 'nullable|boolean',
            ];
        } elseif ($this->type === 'interstate') {
            $rules += [
                'interstate.estimate_rate' => 'required|in:expedited,consolidated',
                'interstate.rate' => 'nullable|numeric|max:9999',
                'interstate.is_auto' => 'nullable|boolean',
                'interstate.packing' => 'nullable|numeric|max:99999',
                'interstate.unpacking' => 'nullable|numeric|max:99999',
                'interstate.shuttle_delivery' => 'nullable|boolean',
                'interstate.shuttle_pickup' => 'nullable|boolean',
                'interstate.delivery_days' => 'required|string|max:60',
            ];
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->sanitizeInput();

        return $this->getValidatorInstance();
    }

    private function sanitizeInput()
    {
        $input = $this->all();

        $input['is_locked'] = !empty($input['is_locked']) ? 1 : 0;
        $input['calculated_moving_distance_is_auto'] = !empty($input['calculated_moving_distance_is_auto']) ? 1 : 0;

        $order = Order::find($input['order_id'], ['division_id']);

        $settings = EstimateParameters::selected($order->division_id)
            ->whereIn('estimate_type', ['any', $this->type])
            ->get([
                'name', 'value'
            ])
            ->pluck('value', 'name')
            ->all();

        $input = $this->validateAll($settings, $input);

        if ($this->type === 'local') {
            $input['local']['is_auto'] = !empty($input['local']['is_auto']) ? 1 : 0;
        } elseif ($this->type === 'intrastate') {
            $input['intrastate']['is_auto'] = !empty($input['intrastate']['is_auto']) ? 1 : 0;
        } elseif ($this->type === 'interstate') {
            $input = $this->validateInterstate($settings, $input);
        }

        $this->replace($input);
    }

    private function validateAll($settings, $input)
    {
        if (!empty($settings['min_hours']) && $input['local']['hours_min'] < $settings['min_hours']) {
            $input['local']['hours_min'] = $settings['min_hours'];
        }
        if (!empty($settings['min_hours']) && $input['local']['hours_max'] < $settings['min_hours']) {
            $input['local']['hours_max'] = $settings['min_hours'];
        }
        if (!empty($settings['min_trucks']) && $input['trucks'] < $settings['min_trucks']) {
            $input['trucks'] = $settings['min_trucks'];
        }
        if (!empty($settings['min_crew']) && $input['crews'] < $settings['min_crew']) {
            $input['crews'] = $settings['min_crew'];
        }

        return $input;
    }

    private function validateInterstate($settings, $input)
    {
        $input['interstate']['is_auto'] = !empty($input['interstate']['is_auto']) ? 1 : 0;

        if (empty($input['interstate']['estimate_rate'])) {
            $input['interstate']['estimate_rate'] = 'expedited';
        }
        if (empty($input['interstate']['delivery_days'])) {
            $input['interstate']['delivery_days'] = $settings['delivery_days'];
        }

        $input['interstate']['shuttle_delivery'] = !empty($input['interstate']['shuttle_delivery']) ? 1 : 0;
        $input['interstate']['shuttle_pickup'] = !empty($input['interstate']['shuttle_pickup']) ? 1 : 0;

        $input['interstate']['delivery_days'] = strip_tags($input['interstate']['delivery_days']);

        return $input;
    }
}
