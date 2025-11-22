<?php

namespace App\GraphQL\Queries\Common\Alerts;

use App\GraphQL\Types\Enums\Alerts\AlertObjectTypeEnum;
use App\Permissions\Alerts\AlertListPermission;
use App\Services\Alerts\AlertService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseAlertQuery extends BaseQuery
{
    public const NAME = 'alert';
    public const PERMISSION = AlertListPermission::KEY;

    public function __construct(private AlertService $alertService)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    /**
     * @return array[]
     */
    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
            'object_name' => [
                'type' => Type::listOf(
                    AlertObjectTypeEnum::nonNullType()
                )
            ],
            'per_page' => [
                'type' => Type::int(),
                'defaultValue' => 10,
            ],
            'page' => [
                'type' => Type::int(),
                'defaultValue' => 1,
            ],
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return LengthAwarePaginator
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->alertService->getList($args, $this->user());
    }
}
