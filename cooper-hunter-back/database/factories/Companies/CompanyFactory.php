<?php

namespace Database\Factories\Companies;

use App\Enums\Companies\CompanyStatus;
use App\Enums\Companies\CompanyType;
use App\Models\Companies\Company;
use App\Models\Locations\State;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Company|Company[]|Collection create(array $attributes = [])
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $state = State::first();
        return [
            'guid' => null,
            'status' => CompanyStatus::DRAFT,
            'type' => CompanyType::PARTNERSHIP,
            'code' => null,
            'terms' => null,
            'business_name' => $this->faker->company,
            'email' => new Email($this->faker->unique()->safeEmail),
            'phone' => new Phone($this->faker->unique()->phoneNumber),
            'country_id' => $state->country_id,
            'state_id' => $state->id,
            'city' => $this->faker->city,
            'address_line_1' => $this->faker->streetName,
            'address_line_2' => $this->faker->streetName,
            'po_box' => $this->faker->postcode,
            'zip' => $this->faker->postcode,
            'fax' => new Phone($this->faker->unique()->phoneNumber),
            'taxpayer_id' => $this->faker->creditCardNumber,
            'tax' => $this->faker->numerify,
            'websites' => [
                $this->faker->url,
                $this->faker->url,
            ],
            'marketplaces' => [
                $this->faker->url,
                $this->faker->url,
            ],
            'trade_names' => [
                $this->faker->sentence
            ],
        ];
    }
}

