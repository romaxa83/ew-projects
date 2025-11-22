<?php

namespace App\Http\Requests\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Category;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Suppliers\Supplier;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryIndexRequest extends FormRequest
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
        return $this->user()->can('inventories');
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
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer', Rule::exists(Category::TABLE_NAME, 'id')],
            'supplier_id' => ['nullable', 'integer', Rule::exists(Supplier::TABLE_NAME, 'id')],
            'status' => ['nullable', 'string', $this->statusIn()],
            'only_min_limit' => ['nullable'],
            'for_sale' => ['nullable'],
        ];
    }

    protected function setDefaults(): void
    {
        if (is_null($this->per_page)) {
            $this->per_page = self::PER_PAGE;
        }
    }

    protected function statusIn(): string
    {
        return 'in:' . implode(',', [Inventory::STATUS_OUT_OF_STOCK, Inventory::STATUS_IN_STOCK]);
    }
}
