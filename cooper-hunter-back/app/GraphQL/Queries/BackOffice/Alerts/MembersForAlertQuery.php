<?php

namespace App\GraphQL\Queries\BackOffice\Alerts;

use App\GraphQL\Types\Enums\Users\MemberMorphTypeEnum;
use App\GraphQL\Types\Members\MemberType;
use App\Permissions\Alerts\AlertListPermission;
use App\Services\Alerts\AlertService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class MembersForAlertQuery extends BaseQuery
{
    public const NAME = 'membersForAlert';
    public const PERMISSION = AlertListPermission::KEY;

    public function __construct(private AlertService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'query' => [
                'type' => Type::string(),
                'description' => 'Search by email, first_name, last_name'
            ],
            'member_type' => [
                'type' => MemberMorphTypeEnum::type(),
            ],
            'per_page' => [
                'type' => Type::int(),
                'defaultValue' => config('queries.default.pagination.per_page')
            ],
            'page' => [
                'type' => Type::int(),
                'defaultValue' => 1
            ],
        ];
    }

    public function type(): Type
    {
        return MemberType::paginate();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): mixed
    {
        return $this->service->getMemberForAlert($args);
    }
}
