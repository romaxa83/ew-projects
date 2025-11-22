<?php

namespace App\Foundations\Modules\Media\Contracts;

use App\Foundations\Modules\Media\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

/**
 * @property-read MediaCollection|Media[] media
 */
interface HasMedia extends \Spatie\MediaLibrary\HasMedia
{
    public function getMediaCollectionName(): string;
}

