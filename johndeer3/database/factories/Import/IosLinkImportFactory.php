<?php

namespace Database\Factories\Import;

use App\Models\Import\IosLinkImport;
use Illuminate\Database\Eloquent\Factories\Factory;

class IosLinkImportFactory extends Factory
{
    protected $model = IosLinkImport::class;

    public function definition(): array
    {
        return [
            'message' => $this->faker->sentence,
            'file' => $this->faker->sentence,
            'status' => IosLinkImport::STATUS_NEW,
        ];
    }
}

