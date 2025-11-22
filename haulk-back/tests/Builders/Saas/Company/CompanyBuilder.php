<?php

namespace Tests\Builders\Saas\Company;

use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\Pricing\CompanySubscription;
use App\Models\Saas\Pricing\PricingPlan;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class CompanyBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Company::class;
    }

    public function company(Company $model): self
    {
        $this->data['company_id'] = $model->id;
        return $this;
    }

    public function trial($company, bool $canceled = false)
    {
        $plan = PricingPlan::where('slug', config('pricing.plans.regular.slug'))->first();

        if (!$plan) {
            throw new \Exception(trans('Pricing plan not found.'));
        }

        $subscription = new CompanySubscription();

        $subscription->pricing_plan_id = $plan->id;
        $subscription->company_id = $company->id;
        $subscription->is_trial = true;
        $subscription->canceled = $canceled;
        $subscription->billing_start = now()->startOfDay();
        $subscription->billing_end = now()->add($plan->getDuration())->endOfDay();

        $subscription->save();

        return $company;
    }

    public function regularPlan($company)
    {
        $plan = PricingPlan::where('slug', config('pricing.plans.regular.slug'))->first();

        if (!$plan) {
            throw new \Exception(trans('Pricing plan not found.'));
        }

        $subscription = new CompanySubscription();

        $subscription->pricing_plan_id = $plan->id;
        $subscription->company_id = $company->id;
        $subscription->is_trial = false;
        $subscription->canceled = false;
        $subscription->billing_start = now()->startOfDay();
        $subscription->billing_end = now()->add($plan->getDuration())->endOfDay();

        $subscription->save();

        return $company;
    }
}


