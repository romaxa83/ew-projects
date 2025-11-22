<?php

namespace App\Rules\Carrier;

use App\Models\Saas\Company\Company;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class CheckDestroyToken implements Rule
{

    private Company $company;

    private string $type;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Company $company, string $type)
    {
        $this->company = $company;
        $this->type = $type;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if ($this->company->crm_date_token_create === null) {
            return false;
        }
        if ($this->type === 'decline') {
            return $this->company->crm_decline_token === $value;
        }

        $tokenLife = Carbon::parse($this->company->crm_date_token_create)->diffInSeconds();
        if ($tokenLife > config('saas.company.destroy_token_life', 1800)) {
            return false;
        }

        return $this->company->crm_confirm_token === $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('Forbidden. URL expired.');
    }
}
