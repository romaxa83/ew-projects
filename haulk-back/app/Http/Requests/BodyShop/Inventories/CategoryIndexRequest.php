<?php

namespace App\Http\Requests\BodyShop\Inventories;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property null|string order_by
 * @property null|string order_type
 */
class CategoryIndexRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('inventory-categories');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $this->setDefaults();

        return [
            'q' => ['nullable', 'string'],
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
        return ['name'];
    }

    protected function setDefaults(): void
    {
        if (is_null($this->order_by)) {
            $this->order_by = 'name';
        }

        if (is_null($this->order_type)) {
            $this->order_type = 'asc';
        }
    }
}
