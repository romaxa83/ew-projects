<?php

namespace App\GraphQL\Queries\BackOffice\Sips;

use App\GraphQL\Types\Sips\SipType;
use App\Repositories\Sips\SipRepository;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;
use App\Permissions;

class SipsQuery extends BaseQuery
{
    public const NAME = 'Sips';
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
        return array_merge(
            $this->paginationArgs(),
            [
                'id' => Type::id(),
                'has_employee' => Type::boolean()
            ],
        );
    }

    public function type(): Type
    {
        return SipType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        return $this->repo->getPagination(
            filters: $args
        );
    }
}
