<?php

namespace Database\Factories\Locations;

use App\Models\Locations\Country;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Country[]|Country create(array $attributes = [])
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        $code = $this->code();

        return [
            'alias' => $code,
            'active' => true,
            'default' => true,
            'sort' => 1,
//            'country_code' => 'UA',
            'country_code' => $code,
        ];
    }

    public function code()
    {
        $code = $this->faker->countryCode;

        $countries = Country::query()->get()->pluck('country_code', 'country_code')->toArray();

        if(array_key_exists($code, $countries)){
            $this->code();
        }

        return $code;
    }
}
