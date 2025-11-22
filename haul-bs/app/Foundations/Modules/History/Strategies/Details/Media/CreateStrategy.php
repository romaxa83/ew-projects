<?php

namespace App\Foundations\Modules\History\Strategies\Details\Media;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Foundations\Modules\Media\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class CreateStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Media|SpatieMedia $model,
        protected string $fieldName
    )
    {}

    public function getDetails(): array
    {
        $tmp["{$this->fieldName}.{$this->model->id}.name"] = [
            'old' => null,
            'new' => $this->model->name,
            'type' => self::TYPE_ADDED
        ];

        return $tmp;
    }
}
