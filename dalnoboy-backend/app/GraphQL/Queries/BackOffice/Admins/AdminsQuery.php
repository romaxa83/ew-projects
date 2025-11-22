<?php

namespace App\GraphQL\Queries\BackOffice\Admins;

use App\GraphQL\Types\Admins\AdminType;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminShowPermission;
use App\Services\Admins\AdminService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\{ResolveInfo, Type};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class AdminsQuery extends BaseQuery
{
    public const NAME = 'admins';
    public const PERMISSION = AdminShowPermission::KEY;

    public function __construct(private AdminService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return AdminType::paginate();
    }

    public function args(): array
    {
        $args = $this->buildArgs(
            Admin::ALLOWED_SORTING_FIELDS,
            [
                'first_name',
                'last_name',
                'second_name',
                'email',
                'phone',
            ]
        );
        $args['sort']['defaultValue'] = [
            'created_at-desc'
        ];

        return $args;
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->service->show($args, $fields->getRelations());
    }
}
