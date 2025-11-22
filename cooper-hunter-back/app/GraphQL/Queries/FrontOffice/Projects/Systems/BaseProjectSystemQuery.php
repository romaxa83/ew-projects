<?php

namespace App\GraphQL\Queries\FrontOffice\Projects\Systems;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Projects\ProjectSystemType;
use App\Models\Projects\System;
use App\Permissions\Projects\ProjectListPermission;
use App\Rules\ExistsRules\ProjectSystemExistsRule;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseProjectSystemQuery extends BaseQuery
{
    public const PERMISSION = ProjectListPermission::KEY;

    public function __construct()
    {
        $this->setMemberGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
        ];
    }

    public function type(): Type
    {
        return ProjectSystemType::type();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): System
    {
        return System::query()
            ->with($fields->getRelations())
            ->find($args['id']);
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn() => [
                'id' => ProjectSystemExistsRule::forMember($this->user())
            ]
        );
    }
}
