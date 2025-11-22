<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class InspectInteriorRequest extends FormRequest
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
        return [
            'condition_dark' => ['nullable', 'boolean'],
            'condition_snow' => ['nullable', 'boolean'],
            'condition_rain' => ['nullable', 'boolean'],
            'condition_dirty_vehicle' => ['nullable', 'boolean'],
            'odometer' => ['required_without:notes', 'nullable', 'numeric'],
            'notes' => ['required_without:odometer', 'nullable', 'string', 'max:65000'],
            'num_keys' => ['nullable', 'integer'],
            'num_remotes' => ['nullable', 'integer'],
            'num_headrests' => ['nullable', 'integer'],
            'drivable' => ['nullable', 'boolean'],
            'windscreen' => ['nullable', 'boolean'],
            'glass_all_intact' => ['nullable', 'boolean'],
            'title' => ['nullable', 'boolean'],
            'cargo_cover' => ['nullable', 'boolean'],
            'spare_tire' => ['nullable', 'boolean'],
            'radio' => ['nullable', 'boolean'],
            'manuals' => ['nullable', 'boolean'],
            'navigation_disk' => ['nullable', 'boolean'],
            'plugin_charger_cable' => ['nullable', 'boolean'],
            'headphones' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'condition_dark' => $this->boolean('condition_dark'),
            'condition_snow' => $this->boolean('condition_snow'),
            'condition_rain' => $this->boolean('condition_rain'),
            'condition_dirty_vehicle' => $this->boolean('condition_dirty_vehicle'),
            'drivable' => $this->boolean('drivable'),
            'windscreen' => $this->boolean('windscreen'),
            'glass_all_intact' => $this->boolean('glass_all_intact'),
            'title' => $this->boolean('title'),
            'cargo_cover' => $this->boolean('cargo_cover'),
            'spare_tire' => $this->boolean('spare_tire'),
            'radio' => $this->boolean('radio'),
            'manuals' => $this->boolean('manuals'),
            'navigation_disk' => $this->boolean('navigation_disk'),
            'plugin_charger_cable' => $this->boolean('plugin_charger_cable'),
            'headphones' => $this->boolean('headphones'),
        ]);
    }
}
