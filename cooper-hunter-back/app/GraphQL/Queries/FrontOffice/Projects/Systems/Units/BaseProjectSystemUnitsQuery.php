<?php

namespace App\GraphQL\Queries\FrontOffice\Projects\Systems\Units;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Projects\ProjectSystemUnitType;
use App\Models\Projects\System;
use App\Permissions\Projects\ProjectListPermission;
use App\Rules\ExistsRules\ProjectSystemExistsRule;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseProjectSystemUnitsQuery extends BaseQuery
{
    public const PERMISSION = ProjectListPermission::KEY;

    public function __construct()
    {
        $this->setMemberGuard();
    }

    public function args(): array
    {
        return array_merge(
            [
                'system_id' => [
                    'type' => NonNullType::id(),
                ],
            ],
            $this->paginationArgs(),
        );
    }

    public function type(): Type
    {
        return ProjectSystemUnitType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            System::query()
                ->findOrFail($args['system_id'])
                ->units(),
            $args
        );
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn() => array_merge(
                [
                    'system_id' => ProjectSystemExistsRule::forMember($this->user())
                ],
                $this->paginationRules(),
            ),
        );
    }
}
