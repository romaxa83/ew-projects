<?php

namespace App\GraphQL\Queries\BackOffice\Utilities;

use App\GraphQL\Types\Utilities\AppVersionType;
use App\Models\Utils\Version;
use App\Permissions\Utilities\AppVersion\AppVersionPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class AppVersionsQuery extends BaseQuery
{
    public const NAME = 'appVersions';
    public const PERMISSION = AppVersionPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return AppVersionType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?Version {
        return Version::first();
    }
}