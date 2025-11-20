<?php

namespace App\GraphQL\Types\Enums\Avatars;

use App\Enums\Avatars\AvatarModelsEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class AvatarModelsTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'AvatarModelsTypeEnum';
    public const DESCRIPTION = 'Список сущностей, которые поддерживают загрузку аватаров';
    public const ENUM_CLASS = AvatarModelsEnum::class;
}
