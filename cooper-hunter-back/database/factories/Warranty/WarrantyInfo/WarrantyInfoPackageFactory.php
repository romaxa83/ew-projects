<?php

namespace Database\Factories\Warranty\WarrantyInfo;

use App\Models\Warranty\WarrantyInfo\WarrantyInfo;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoPackage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Traits\Storage\TestStorage;

/**
 * @method Collection|WarrantyInfoPackage[]|WarrantyInfoPackage create(array $attributes = [])
 */
class WarrantyInfoPackageFactory extends Factory
{
    use TestStorage;

    protected $model = WarrantyInfoPackage::class;

    public function definition(): array
    {
        return [
            'warranty_info_id' => WarrantyInfo::factory(),
            'sort' => 0,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(
            fn(WarrantyInfoPackage $m) => $m
                ->addMedia($this->getSamplePdf())
                ->toMediaCollection(WarrantyInfoPackage::MEDIA_COLLECTION_NAME)
        );
    }
}
