<?php

namespace Database\Factories\Catalog\Certificates;

use App\Models\Catalog\Certificates\CertificateType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|CertificateType[]|CertificateType create(array $attributes = [])
 */
class CertificateTypeFactory extends Factory
{
    protected $model = CertificateType::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->unique()->userName,
        ];
    }
}
