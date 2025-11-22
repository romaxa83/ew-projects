<?php

namespace App\Rules\Billing;

use App\Models\Saas\Company\Company;
use App\Services\Billing\BillingService;
use Illuminate\Contracts\Validation\Rule;

class isSubscriptionCompany implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Company  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        /** @var $billingService BillingService */
        $billingService = resolve(BillingService::class);

        return $billingService->checkSubscription($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('You don\'t have any subscriptions');
    }
}
