<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Solutions;

use App\GraphQL\Types\Catalog\Solutions\SolutionType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionTypeEnumType;
use App\Permissions\Catalog\Solutions\SolutionReadPermission;
use App\Services\Catalog\Solutions\SolutionService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class SolutionListQuery extends BaseQuery
{
    public const NAME = 'solutionList';
    public const PERMISSION = SolutionReadPermission::KEY;

    public function __construct(private SolutionService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return SolutionType::nonNullList();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
            'type' => [
                'type' => SolutionTypeEnumType::type()
            ]
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->getList($args);
    }

}
