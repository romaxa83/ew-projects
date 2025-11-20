<?php

namespace App\GraphQL\Queries\BackOffice\Sips;

use App\GraphQL\Types\Enums\Employees\StatusEnum;
use App\GraphQL\Types\Sips\SipType;
use App\Repositories\Sips\SipRepository;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;
use App\Permissions;

class SipsListQuery extends BaseQuery
{
    public const NAME = 'SipsList';
    public const PERMISSION = Permissions\Sips\ListPermission::KEY;

    public function __construct(protected SipRepository $repo)
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool
    {
        $this->setAuthGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
            'id' => Type::id(),
            'has_employee' => Type::boolean(),
            'employee_statuses' => Type::listOf(
                StatusEnum::type()
            ),
        ];
    }

    public function type(): Type
    {
        return SipType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return $this->repo->getList(
            filters: $args
        );
    }
}
