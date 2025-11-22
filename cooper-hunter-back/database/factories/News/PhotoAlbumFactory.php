<?php

namespace Database\Factories\News;

use App\Models\News\PhotoAlbum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|PhotoAlbum[]|PhotoAlbum create(array $attributes = [])
 */
class PhotoAlbumFactory extends Factory
{
    protected $model = PhotoAlbum::class;

    public function definition(): array
    {
        return [

        ];
    }
}
