<?php

namespace App\GraphQL\Mutations\BackOffice\Alerts;

use App\Dto\Alerts\AlertSendDto;
use App\GraphQL\InputTypes\Alert\AlertSendInputType;
use App\GraphQL\Types\NonNullType;
use App\Permissions\Alerts\AlertSendPermission;
use App\Services\Alerts\AlertService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AlertSendMutation extends BaseMutation
{
    public const NAME = 'alertSend';
    public const PERMISSION = AlertSendPermission::KEY;

    public function __construct(private AlertService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'input' => [
                'type' => AlertSendInputType::nonNullType()
            ]
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->sendCustomAlert(
                AlertSendDto::byArgs($args['input'])
            )
        );
    }
}
