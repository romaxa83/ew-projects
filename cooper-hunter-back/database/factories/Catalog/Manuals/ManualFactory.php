<?php

namespace Database\Factories\Catalog\Manuals;

use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Manuals\ManualGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Traits\Storage\TestStorage;

/**
 * @method Collection|Manual[]|Manual create(array $attributes = [])
 */
class ManualFactory extends Factory
{
    use TestStorage;

    protected $model = Manual::class;

    public function definition(): array
    {
        return [
            'manual_group_id' => ManualGroup::factory(),
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(
            fn(Manual $m) => $m
                ->addMedia($this->getSamplePdf())
                ->toMediaCollection(Manual::MEDIA_COLLECTION_NAME)
        );
    }
}
