<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed order_by
 * @property mixed order_type
 * @property mixed per_page
 */
class DocumentFilterRequest extends FormRequest
{
    const PER_PAGE = 10;

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
        $this->setDefaults();

        return [
            'order_by' => ['nullable', 'string', $this->orderByIn()],
            'order_type' => ['nullable', 'string', $this->orderTypeIn()],
            'per_page' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date', 'date_format:m/d/Y'],
            'date_to' => ['nullable', 'date', 'date_format:m/d/Y'],
            'name' => ['nullable', 'string'],
        ];
    }

    protected function setDefaults(): void
    {
        if (is_null($this->order_by)) {
            $this->order_by = 'created_at';
        }

        if (is_null($this->order_type)) {
            $this->order_type = 'desc';
        }

        if (is_null($this->per_page)) {
            $this->per_page = self::PER_PAGE;
        }
    }

    public function sortableAttributes(): array
    {
        return ['created_at', 'name'];
    }

    public function filterableAttributes(): array
    {
        return ['date_from', 'date_to', 'driver_id', 'name'];
    }

    public function validatedForFilter(): array
    {
        return $this->only($this->filterableAttributes());
    }

    protected function orderTypeIn(): string
    {
        return 'in:' . implode(',', ['asc', 'desc']);
    }

    protected function orderByIn(): string
    {
        return 'in:' . implode(',', $this->sortableAttributes());
    }
}
