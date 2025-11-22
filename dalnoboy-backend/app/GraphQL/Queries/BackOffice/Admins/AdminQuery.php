<?php

namespace App\GraphQL\Queries\BackOffice\Admins;

use App\GraphQL\Types\Admins\AdminType;
use App\Models\Admins\Admin;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\{ResolveInfo, Type};
use Rebing\GraphQL\Support\SelectFields;

class AdminQuery extends BaseQuery
{
    public const NAME = 'admin';

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->authCheck();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return AdminType::nonNullType();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Admin
    {
        return $this->user()->load($fields->getRelations());
    }
}
