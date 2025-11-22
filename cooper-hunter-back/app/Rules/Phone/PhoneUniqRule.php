<?php

namespace App\Rules\Phone;

use App\ValueObjects\Phone;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Throwable;

class PhoneUniqRule implements Rule
{
    protected string $attr;

    public function __construct(
        protected string $table,
        protected string $field = 'phone'
    )
    {}

    public function passes($attribute, $value): bool
    {
        $this->attr = str_replace('_', ' ', $attribute);

        try {
            $phone = (new Phone($value))->getValue();
        } catch (Throwable) {
            return false;
        }

        return !DB::table($this->table)->where($this->field, $phone)->exists();
    }

    public function message(): string
    {
        return __('validation.unique', ['attribute' => $this->attr]);
    }
}
