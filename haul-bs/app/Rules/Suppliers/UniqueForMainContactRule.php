<?php

namespace App\Rules\Suppliers;

use App\Http\Requests\Suppliers\SupplierRequest;
use App\Models\Suppliers\SupplierContact;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

class UniqueForMainContactRule implements Rule
{
    private SupplierRequest $request;

    private string $fieldName;

    /**
     * Create a new rule instance.
     * @param SupplierRequest $request
     * @return void
     */
    public function __construct(SupplierRequest $request, string $fieldName)
    {
        $this->request = $request;
        $this->fieldName = $fieldName;
    }


    public function passes($attribute, $value): bool
    {
        $isMainAttribute = str_replace($this->fieldName, 'is_main', $attribute);

        $isMain = Arr::get($this->request->toArray(), $isMainAttribute);

        if (!$isMain) {
            return true;
        }

        $suppliersContacts = SupplierContact::query()
            ->where('is_main', true)
            ->where($this->fieldName, $value);


        if ($id = $this->request->route('id')) {
            $suppliersContacts->where('supplier_id', '!=', $id);
        }

        return !$suppliersContacts->exists();
    }

    public function message(): string
    {
        return trans('validation.unique', ['attribute' => $this->fieldName]);
    }
}
