<?php

namespace App\GraphQL\Mutations\BackOffice\Security;

use App\Dto\Security\IpAccessDto;
use App\Models\Security\IpAccess;
use App\Permissions\Security\IpAccessUpdatePermission;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;

class IpAccessUpdateMutation extends BaseIpAccessMutation
{
    public const NAME = 'ipAccessUpdate';
    public const PERMISSION = IpAccessUpdatePermission::KEY;

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): IpAccess
    {
        return $this->service->update(
            IpAccess::query()->findOrFail($args['id']),
            IpAccessDto::build($args)
        );
    }
}
