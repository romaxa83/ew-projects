<?php

namespace Database\Factories\Catalog\Certificates;

use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Certificates\CertificateType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Certificate[]|Certificate create(array $attributes = [])
 */
class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition(): array
    {
        return [
            'certificate_type_id' => CertificateType::factory(),
            'number' => $this->faker->bothify('???######'),
            'link' => $this->faker->imageUrl,
        ];
    }
}
