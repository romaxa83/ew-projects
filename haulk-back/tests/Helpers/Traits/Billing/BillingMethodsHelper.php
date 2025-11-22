<?php

namespace Tests\Helpers\Traits\Billing;

use App\Models\Locations\State;
use App\Services\Billing\BillingService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

trait BillingMethodsHelper
{
    use WithFaker;

    private function generateDateCount(Carbon $firstDay): array
    {
        $firstDay->addDays(random_int(3, 10));
        $result = [];

        foreach (range(0, random_int(3, 15)) as $i) {
            $day = $firstDay->addDay();
            $result[] = [
                'date' => $day->format('Y-m-d'),
                'driver_count' => random_int(0, 20),
            ];
        }

        return $result;
    }

    private function getBillingService(): BillingService
    {
        return resolve(BillingService::class);
    }

    private function getRandomPaymentMethodData(): array
    {
        return [
            'first_name' => $this->faker->firstNameMale,
            'last_name' => $this->faker->lastName,
            'address' => mb_substr($this->faker->address, 0, 60),
            'city' => $this->faker->city,
            'state_id' => factory(State::class)->create()->id,
            'zip' => $this->faker->postcode,
            'card_number' => $this->faker->creditCardNumber,
            'expires_at' => now()->addYear()->format('m/y'),
            'cvc' => (string)$this->faker->randomNumber(3, true),
        ];
    }
}
