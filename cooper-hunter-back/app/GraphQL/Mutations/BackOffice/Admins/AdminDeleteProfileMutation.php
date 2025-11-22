<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\Permissions\Admins\AdminDeletePermission;
use App\Services\Admins\AdminService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AdminDeleteProfileMutation extends BaseMutation
{
    public const NAME = 'adminDeleteProfile';
    public const PERMISSION = AdminDeletePermission::KEY;

    public function __construct(protected AdminService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    /** @throws Throwable */
    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool {
        if ($this->guest()) {
            throw new TranslatedException(__('unauthorized'));
        }

        if ($this->user()->isSuperAdmin()) {
            throw new TranslatedException('Super admin cannot be deleted!');
        }

        return makeTransaction(
            fn() => $this->service->delete($this->user())
        );
    }
}
