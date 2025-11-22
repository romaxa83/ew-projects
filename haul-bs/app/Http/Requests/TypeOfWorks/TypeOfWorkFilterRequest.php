<?php

namespace App\Http\Requests\TypeOfWorks;

use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Inventory;
use App\Models\TypeOfWorks\TypeOfWork;
use Illuminate\Validation\Rule;

class TypeOfWorkFilterRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return array_merge(
            $this->paginationRule(),
            $this->searchRule(),
            $this->idRule(),
            $this->orderRule(TypeOfWork::ALLOWED_SORTING_FIELDS),
            [
                'inventory_id' => ['nullable', 'integer', Rule::exists(Inventory::TABLE, 'id')],
            ]
        );
    }
}
