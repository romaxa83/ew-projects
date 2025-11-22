<?php

namespace App\GraphQL\Queries\Common\Settings;

use App\GraphQL\Types\Settings\SettingsType;
use App\Models\Settings\Settings;
use App\Permissions\Settings\SettingsShowPermission;
use App\Services\Settings\SettingsService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseSettingsQuery extends BaseQuery
{
    public const NAME = 'settings';
    public const PERMISSION = SettingsShowPermission::KEY;

    public function __construct(private SettingsService $service)
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
        return SettingsType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Settings {
        return $this->service->show();
    }
}
