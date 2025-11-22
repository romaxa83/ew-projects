<?php

namespace App\GraphQL\Queries\Common\GlobalSettings;

use App\GraphQL\Types\GlobalSettings\GlobalSettingType;
use App\Models\GlobalSettings\GlobalSetting;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseGlobalSettingQuery extends BaseQuery
{
    public const NAME = 'globalSettingInfo';

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return GlobalSettingType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): GlobalSetting {
        return GlobalSetting::query()->firstOrFail();
    }
}
