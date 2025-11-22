<?php

namespace App\Http\Requests\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelCardStatusEnum;
use App\Enums\Fueling\FuelingSourceEnum;
use App\Enums\Fueling\FuelingStatusEnum;
use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property null|string order_by
 * @property null|string order_type
 * @property null|int per_page
 */
class IndexFuelingRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $this->setDefaults();

        return [
            'card' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'driver_id' => ['nullable', 'integer', Rule::exists(User::TABLE_NAME, 'id')],
            'status' => [
                'nullable',
                'string',
                FuelingStatusEnum::ruleIn(),
            ],
            'source' => [
                'nullable',
                'string',
                FuelingSourceEnum::ruleIn(),
            ],
            'fuel_card_status' => [
                'nullable',
                'string',
                FuelCardStatusEnum::ruleIn(),
            ],
            'transaction_date_to' => ['nullable', 'date'],
            'transaction_date_from' => ['nullable', 'date'],
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
            'order_by' => ['nullable', 'string', $this->orderByIn()],
            'order_type' => ['nullable', 'string', $this->orderTypeIn()],
        ];
    }

    protected function orderTypeIn(): string
    {
        return 'in:' . implode(',', ['asc', 'desc']);
    }

    protected function orderByIn(): string
    {
        return 'in:' . implode(',', $this->sortableAttributes());
    }

    public function sortableAttributes(): array
    {
        return ['id', 'transaction_date'];
    }

    protected function setDefaults(): void
    {
        if (is_null($this->order_by)) {
            $this->order_by = 'id';
        }

        if (is_null($this->order_type)) {
            $this->order_type = 'asc';
        }
    }
}
