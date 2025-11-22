<?php

use App\Models\Saas\Pricing\PricingPlan;
use Illuminate\Database\Seeder;

class PricingPlanSeeder extends Seeder
{

    public function run(): void
    {
        foreach (config('pricing.plans') as $plan) {
            try {
                PricingPlan::updateOrCreate(
                    [
                        'id' => $plan['id'],
                    ],
                    $plan
                );
            } catch (Exception $exception) {
                logger($exception);
            }
        }
    }
}
