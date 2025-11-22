<?php

namespace App\GraphQL\Types\Media;

use App\Enums\Media\MediaModelsEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class MediaModelsTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'MediaModelsTypeEnum';
    public const DESCRIPTION = 'Список сущностей, которые поддерживают загрузку медиа';
    public const ENUM_CLASS = MediaModelsEnum::class;
}
