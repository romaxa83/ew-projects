<?php

namespace App\Traits;

use App\Events\ChangeHashEvent;
use App\Models\Hash;
use App\Models\Media\Image;

trait HashData
{
    public function throwEvent(string $alias): void
    {
        Hash::assetAlias($alias);
        event(new ChangeHashEvent($alias));
    }

    public function throwEventForImage(string $model): void
    {
        if($alias = $this->getHashAliasByImageModel($model)) {
            event(new ChangeHashEvent($alias));
        }
    }

    public function getHashAliasByImageModel(string $model): null|string
    {
        return match (true) {
            $model == Image::MODEL_DEALERSHIP => Hash::ALIAS_DEALERSHIP,
            $model == Image::MODEL_BRAND => Hash::ALIAS_BRAND,
            $model == Image::MODEL_MODEL => Hash::ALIAS_MODEL,
            default => null,
        };
    }
}
