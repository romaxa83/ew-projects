<?php

namespace App\Http\Requests\BodyShop\TypesOfWork;

use App\Models\BodyShop\Inventories\Inventory;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property null|string order_by
 * @property null|string order_type
 * @property null|int per_page
 */
class TypeOfWorkIndexRequest extends FormRequest
{
    private const PER_PAGE = 10;

    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('types_of_work');
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
            'inventory_id' => ['nullable', 'integer', Rule::exists(Inventory::TABLE_NAME, 'id')],
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
            'order_by' => ['nullable', 'string', $this->orderByIn()],
            'order_type' => ['nullable', 'string', $this->orderTypeIn()],
        ];
    }

    protected function setDefaults(): void
    {
        if (is_null($this->per_page)) {
            $this->per_page = self::PER_PAGE;
        }

        if (is_null($this->order_by)) {
            $this->order_by = 'name';
        }

        if (is_null($this->order_type)) {
            $this->order_type = 'asc';
        }
    }

    protected function orderByIn(): string
    {
        return 'in:' . implode(',', ['name']);
    }

    protected function orderTypeIn(): string
    {
        return 'in:' . implode(',', ['asc', 'desc']);
    }
}
