<?php

namespace App\Http\Requests\BodyShop\TypesOfWork;

use App\Dto\BodyShop\TypesOfWork\TypeOfWorkDto;
use App\Models\BodyShop\Inventories\Inventory;
use App\Rules\BodyShop\TypesOfWork\QuantityRule;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string type
 */
class TypeOfWorkRequest extends FormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return  [
            'name' => ['required', 'string'],
            'duration' => ['required', 'string', 'regex:/^\d+\:\d+$/'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'inventories' => ['nullable', 'array', 'max:30'],
            'inventories.*.id' => ['required', 'int', Rule::exists(Inventory::TABLE_NAME, 'id')],
            'inventories.*.quantity' => ['required', 'numeric', 'min:0', new QuantityRule($this)],
        ];
    }

    public function dto(): TypeOfWorkDto
    {
        return TypeOfWorkDto::byParams($this->validated());
    }
}
