<?php

namespace App\Traits\Media;

use App\Models\Media\Image;

trait ImageRelation
{
    public function images()
    {
        return $this->morphMany(Image::class, 'entity');
    }

    public function imagesByType(string $type)
    {
        return $this->images()->where('type', $type);
    }
}
