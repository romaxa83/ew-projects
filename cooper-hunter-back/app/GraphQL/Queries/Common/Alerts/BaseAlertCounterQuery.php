<?php

namespace App\GraphQL\Queries\Common\Alerts;

use App\GraphQL\Types\Alerts\AlertCounterType;
use App\GraphQL\Types\Enums\Alerts\AlertObjectTypeEnum;
use App\Permissions\Alerts\AlertListPermission;
use App\Services\Alerts\AlertService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseAlertCounterQuery extends BaseQuery
{
    public const NAME = 'alertCounter';
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
            'object_name' => [
                'type' => Type::listOf(
                    AlertObjectTypeEnum::nonNullType()
                )
            ]
        ];
    }

    public function type(): Type
    {
        return AlertCounterType::nonNullType();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return object
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): object
    {
        return $this->alertService->getCounter($args, $this->user());
    }
}
