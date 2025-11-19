<?php

declare(strict_types=1);

namespace Wezom\Core\Models\Media;

use Spatie\MediaLibrary\HasMedia;

interface Fileable extends HasMedia
{
    public function addMediaWithRandomName(
        $fileData,
        $collectionName,
        bool $clearCollection = false,
        bool $preservingOriginal = false,
        ?array $metaData = null
    ): void;
}
