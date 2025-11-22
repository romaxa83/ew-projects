<?php

namespace App\Rules\Saas\Company;

use App\Models\Saas\Company\Company;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class CheckDestroyToken implements Rule
{
    private string $type;

    /**
     * Create a new rule instance.
     * @param string $type
     * @return void
     */
    public function __construct(string $type)
    {
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
        $company = Company::filter(['destroy' => ['type' => $this->type, 'value' => $value]])->first();
        if ($company === null) {
            return false;
        }
        if ($this->type === 'decline') {
            return $value === $company->saas_decline_token;
        }

        $tokenLife = Carbon::parse($company->saas_date_token_create)->diffInSeconds();

        if ($tokenLife > config('saas.company.destroy_token_life', 1800)) {
            return false;
        }

        return $company->saas_confirm_token === $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('Forbidden. URL expired.');
    }
}
