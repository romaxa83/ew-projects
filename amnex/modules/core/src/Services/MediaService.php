<?php

declare(strict_types=1);

namespace Wezom\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Wezom\Core\Models\Media;

class MediaService
{
    public function delete(Model $model, array $mediaIds): void
    {
        Media::query()
            ->where('model_type', $model->getMorphClass())
            ->where('model_id', $model->getKey())
            ->whereIn('id', $mediaIds)
            ->delete();
    }
}
