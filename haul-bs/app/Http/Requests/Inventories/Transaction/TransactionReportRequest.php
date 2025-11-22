<?php

namespace App\Http\Requests\Inventories\Transaction;

use App\Enums\Inventories\Transaction\OperationType;
use App\Foundations\Enums\EnumHelper;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Category;
use App\Models\Suppliers\Supplier;
use Illuminate\Validation\Rule;

/**
 * @property int $per_page
 */
class TransactionReportRequest extends BaseFormRequest
{
    public const DEFAULT_PER_PAGE = 12;

    use OnlyValidateForm;

    public function rules(): array
    {
        $this->setDefaults();

        return array_merge(
            $this->paginationRule(),
            [
                'search' => ['nullable', 'string'],
                'date_from' => ['nullable', 'date_format:m/d/Y'],
                'date_to' => ['nullable', 'date_format:m/d/Y'],
                'transaction_type' => ['nullable', 'string', EnumHelper::ruleIn(OperationType::class)],
                'supplier_id' => ['nullable', 'integer', Rule::exists(Supplier::TABLE, 'id')],
                'category_id' => ['nullable', 'integer', Rule::exists(Category::TABLE, 'id')],
            ]
        );
    }

    protected function setDefaults(): void
    {
        if (is_null($this->per_page)) {
            $this->per_page = self::DEFAULT_PER_PAGE;
        }
    }
}
