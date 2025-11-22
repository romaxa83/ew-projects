<?php

namespace Database\Factories\Media;

use App\Models\Media\Media;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Media|Media[]|Collection create(array $attrs = [])
 */
class MediaFactory extends BaseFactory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $name = $this->faker->uuid();
        return [
            'id' => $this->faker->randomNumber(),
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

    public function imagePng(): MediaFactory
    {
        return $this->state(
            [
                'file_name' => $this->faker->uuid() . '.png',
                'mime_type' => 'image/png',
            ]
        );
    }

    public function toModel(Model $model): MediaFactory
    {
        return $this->state(
            [
                'model_type' => $model->getMorphClass(),
                'model_id' => $model->getKey(),
            ]
        );
    }

    public function toCollection(string $name): MediaFactory
    {
        return $this->state(
            [
                'collection_name' => $name,
            ]
        );
    }
}
