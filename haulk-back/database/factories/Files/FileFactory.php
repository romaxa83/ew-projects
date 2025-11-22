<?php

namespace Database\Factories\Files;

use App\Models\Files\File;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method File|File[]|Collection create($attributes = [], ?Model $parent = null)
 */
class FileFactory extends Factory
{

    protected $model = File::class;

    public function definition(): array
    {
        $fileName = $this->faker->name;
        return [
            'model_type' => User::class,
            'model_id' => User::factory(),
            'collection_name' => 'default',
            'name' => $fileName,
            'file_name' => $fileName . '.' . $this->faker->unique()->fileExtension,
            'mime_type' => 'text/plain',
            'disk' => 's3',
            'size' => 256,
            'manipulations' => [],
            'responsive_images' => [],
            'custom_properties' => [],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
