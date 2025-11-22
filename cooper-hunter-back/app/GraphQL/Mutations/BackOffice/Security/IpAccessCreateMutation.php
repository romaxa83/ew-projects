<?php

namespace App\GraphQL\Mutations\BackOffice\Security;

use App\Dto\Security\IpAccessDto;
use App\Models\Security\IpAccess;
use App\Permissions\Security\IpAccessCreatePermission;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Arr;
use Rebing\GraphQL\Support\SelectFields;

class IpAccessCreateMutation extends BaseIpAccessMutation
{
    public const NAME = 'ipAccessCreate';
    public const PERMISSION = IpAccessCreatePermission::KEY;

    public function args(): array
    {
        return Arr::except(
            parent::args(),
            ['id']
        );
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): IpAccess {
        return $this->service->create(
            IpAccessDto::build($args)
        );
    }

    protected function rules(array $args = []): array
    {
        return Arr::except(
            parent::rules($args),
            ['id']
        );
    }
}
