<?php

namespace App\Rules\BodyShop\Suppliers;

use App\Http\Requests\BodyShop\Suppliers\SupplierRequest;
use App\Models\BodyShop\Suppliers\SupplierContact;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\Models\Media;

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

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Media|null  $value
     * @return bool
     */
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


        if ($supplier = $this->request->route('supplier')) {
            $suppliersContacts->where('supplier_id', '!=', $supplier->id);
        }

        return !$suppliersContacts->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('validation.unique', ['attribute' => $this->fieldName]);
    }
}
