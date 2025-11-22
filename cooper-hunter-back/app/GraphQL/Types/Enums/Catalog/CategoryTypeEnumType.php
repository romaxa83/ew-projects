<?php


namespace App\GraphQL\Types\Enums\Catalog;


use App\Enums\Categories\CategoryTypeEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class CategoryTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'CategoryTypeEnumType';
    public const ENUM_CLASS = CategoryTypeEnum::class;
}
