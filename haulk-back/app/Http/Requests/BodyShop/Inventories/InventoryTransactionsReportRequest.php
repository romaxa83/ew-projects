<?php

namespace App\Http\Requests\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Category;
use App\Models\BodyShop\Inventories\Transaction;
use App\Models\BodyShop\Suppliers\Supplier;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $per_page
 */
class InventoryTransactionsReportRequest extends FormRequest
{
    private const PER_PAGE = 12;

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
            'q' => ['nullable', 'string'],
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date_format:m/d/Y'],
            'date_to' => ['nullable', 'date_format:m/d/Y'],
            'transaction_type' => ['nullable', 'string', Rule::in([Transaction::OPERATION_TYPE_SOLD, Transaction::OPERATION_TYPE_PURCHASE])],
            'supplier_id' => ['nullable', 'integer', Rule::exists(Supplier::TABLE_NAME, 'id')],
            'category_id' => ['nullable', 'integer', Rule::exists(Category::TABLE_NAME, 'id')],
        ];
    }

    protected function setDefaults(): void
    {
        if (is_null($this->per_page)) {
            $this->per_page = self::PER_PAGE;
        }
    }
}
