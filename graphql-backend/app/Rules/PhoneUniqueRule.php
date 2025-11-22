<?php

namespace App\Rules;

use App\Models\Users\User;
use App\ValueObjects\Phone;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class PhoneUniqueRule implements Rule
{
    private int|null $id;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $phone = new Phone($value);

        $exists = User::query()
            ->where('phone', (string)$phone)
            ->when(
                $this->id,
                function (Builder $query) {
                    return $query->where('id', '!=', $this->id);
                }
            )->exists();

        return !$exists;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.unique', ['attribute' => 'phone']);
    }
}
