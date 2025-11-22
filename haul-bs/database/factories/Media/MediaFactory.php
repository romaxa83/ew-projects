<?php

namespace Database\Factories\Media;

use App\Foundations\Modules\Media\Models\Media;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Media|Media[]|Collection create(array $attrs = [])
 */
class MediaFactory extends BaseFactory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'model_type' => $this->faker->word(),
            'model_id' => 1,
            'uuid' => $this->faker->uuid,
            'collection_name' => $this->faker->word,
            'name' => $this->faker->word,
            'file_name' => $this->faker->filePath(),
            'mime_type' => $this->faker->mimeType(),
            'disk' => 'public',
            'conversions_disk' => 'public',
            'size' => 22,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
            'sort' => 2,
        ];
    }

    public function setGenerateConversions(): self
    {
        return $this->state([
            'generated_conversions' => [
                "original_webp" => true,
                "original_jpg" => true,
                "lg" => true,
                "lg_jpg" => true,
                "lg_2x_webp" => true,
                "xl" => true,
                "xl_jpg" => true,
                "xl_2x_webp" => true,
                "md" => true,
                "md_jpg" => true,
                "md_2x_webp" => true,
                "sm" => true,
                "sm_jpg" => true,
                "sm_2x_webp" => true,
                "xs" => true,
                "xs_jpg" => true,
                "xs_2x_webp" => true,
            ],
        ]);
    }
}
