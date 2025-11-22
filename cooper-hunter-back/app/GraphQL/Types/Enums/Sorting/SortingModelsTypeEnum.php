<?php

namespace App\GraphQL\Types\Enums\Sorting;

use App\Enums\Sorting\SortingModelsEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class SortingModelsTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'SortingModelsTypeEnum';
    public const DESCRIPTION = 'Список сущностей, которые поддерживают сортировку';
    public const ENUM_CLASS = SortingModelsEnum::class;

    public const BY_KEYS = true;
}
