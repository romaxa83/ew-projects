<?php

declare(strict_types=1);

namespace Wezom\Core\Database\Factories;

use BackedEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;
use Wezom\Core\Models\Media;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $name = $this->faker->uuid();

        return [
            'id' => $this->faker->unique()->randomNumber(),
            'model_type' => 'SomeClassName',
            'model_id' => $this->faker->randomNumber(),
            'uuid' => $this->faker->uuid(),
            'collection_name' => 'images',
            'name' => $name,
            'file_name' => $name . '.' . $this->faker->fileExtension(),
            'mime_type' => $this->faker->mimeType(),
            'disk' => config('filesystems.default'),
            'conversions_disk' => config('filesystems.default'),
            'size' => $this->faker->randomNumber(5),
            'order_column' => $this->faker->randomNumber(),
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
        ];
    }

    public function imagePng(): self
    {
        return $this->state(
            [
                'file_name' => $this->faker->uuid() . '.png',
                'mime_type' => 'image/png',
            ]
        );
    }

    public function toModel(Model $model): self
    {
        return $this->state(
            [
                'model_type' => $model->getMorphClass(),
                'model_id' => $model->getKey(),
            ]
        );
    }

    public function toCollection(string|BackedEnum|UnitEnum $name): self
    {
        return $this->state(
            [
                'collection_name' => enum_to_string($name),
            ]
        );
    }
}
