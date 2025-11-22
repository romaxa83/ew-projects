<?php

namespace App\GraphQL\Mutations\Common\Alerts;

use App\GraphQL\Types\NonNullType;
use App\Models\Alerts\Alert;
use App\Permissions\Alerts\AlertSetReadPermission;
use App\Services\Alerts\AlertService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseAlertSetReadMutation extends BaseMutation
{
    public const NAME = 'alertSetRead';
    public const PERMISSION = AlertSetReadPermission::KEY;

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
            'ids' => [
                'type' => Type::listOf(
                    NonNullType::id()
                ),
            ],
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
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool {
        return $this->alertService->setRead(data_get($args, 'ids'), $this->user());
    }

    public function rules(array $args = []): array
    {
        return [
            'ids.*' => [
                'nullable',
                'int',
                Rule::exists(Alert::class, 'id')
            ]
        ];
    }
}
