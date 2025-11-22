<?php

namespace App\GraphQL\Queries\Common\Commercial;

use App\GraphQL\Types\Commercial\CommercialSettingsType;
use App\Models\Commercial\CommercialSettings;
use App\Permissions\Commercial\CommercialSettings\CommercialSettingsListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseCommercialSettingsQuery extends BaseQuery
{
    public const NAME = 'commercialSettings';
    public const PERMISSION = CommercialSettingsListPermission::KEY;

    public function __construct()
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return CommercialSettingsType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?CommercialSettings {
        return CommercialSettings::first();
    }
}
