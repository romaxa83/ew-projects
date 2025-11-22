<?php

namespace Database\Factories\Agreement;

use App\Models\Agreement\Agreement;
use App\ValueObjects\CarNumber;
use App\ValueObjects\CarVin;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class AgreementFactory extends Factory
{
    protected $model = Agreement::class;

    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4(),
            'car_uuid' => Uuid::uuid4(),
            'user_uuid' => Uuid::uuid4(),
            'phone' => new Phone($this->faker->phoneNumber),
            'number' => new CarNumber(Str::random(6)),
            'vin' => new CarVin(Str::random(6)),
            'author' => $this->faker->userName,
            'author_phone' => $this->faker->phoneNumber,
        ];
    }
}
