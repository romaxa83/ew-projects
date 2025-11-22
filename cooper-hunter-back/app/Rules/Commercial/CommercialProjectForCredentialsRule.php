<?php

namespace App\Rules\Commercial;

use App\Contracts\Members\HasCommercialProjects;
use App\Enums\Commercial\CommercialProjectStatusEnum;
use Illuminate\Contracts\Validation\Rule;

class CommercialProjectForCredentialsRule implements Rule
{
    public function __construct(private HasCommercialProjects $user)
    {
    }

    public function passes($attribute, $value): bool
    {
        return $this->user->commercialProjects()
            ->whereNotNull('code')
            ->whereKey($value)
            ->where('estimate_end_date', '>', now())
            ->where('status', CommercialProjectStatusEnum::PENDING)
            ->exists();
    }

    public function message(): string
    {
        return __('The selected project is expired or does not exist');
    }
}