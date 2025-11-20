<?php

namespace App\Rules\Sip;

use App\Models\Sips\Sip;
use Illuminate\Contracts\Validation\Rule;

class SipAttachedRule implements Rule
{
    public function __construct(protected ?int $ignoreId = null)
    {}

    public function passes($attribute, $value): bool
    {
        return !Sip::query()
            ->has('employee')
            ->where('id', $value)
            ->when($this->ignoreId, fn($q) => $q->whereHas(
                'employee',
                fn($q) => $q->where('id', '<>', $this->ignoreId)
            ))
            ->exists()
        ;
    }

    public function message(): string
    {
        return __('validation.custom.sip.attached');
    }
}
