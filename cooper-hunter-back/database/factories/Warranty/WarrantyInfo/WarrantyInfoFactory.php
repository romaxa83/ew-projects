<?php

namespace Database\Factories\Warranty\WarrantyInfo;

use App\Models\Warranty\WarrantyInfo\WarrantyInfo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Traits\Storage\TestStorage;

/**
 * @method Collection|WarrantyInfo[]|WarrantyInfo create(array $attributes = [])
 */
class WarrantyInfoFactory extends Factory
{
    use TestStorage;

    protected $model = WarrantyInfo::class;

    public function definition(): array
    {
        return [
            'video_link' => $this->faker->imageUrl,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(
            fn(WarrantyInfo $m) => $m
                ->addMedia($this->getSamplePdf())
                ->toMediaCollection(WarrantyInfo::MEDIA_COLLECTION_NAME)
        );
    }
}
