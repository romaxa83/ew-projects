<?php

namespace App\Contracts\Media;

use App\Models\Media\Media;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

/**
 * @property-read MediaCollection|Media[] media
 */
interface HasMedia extends \Spatie\MediaLibrary\HasMedia
{
    public function getMediaCollectionName(): string;

    public function getMultiLangMediaCollectionName(string $lang): string;
}
