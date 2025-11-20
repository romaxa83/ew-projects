<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\GraphQL\Types\Admins\AdminType;
use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminUpdatePermission;
use App\Services\Admins\AdminService;
use Closure;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AdminToggleActiveMutation extends BaseMutation
{
    public const NAME = 'AdminToggleActive';
    public const PERMISSION = AdminUpdatePermission::KEY;

    public function __construct(
        protected AdminService $service
    )
    {}

    public function authorize(mixed $root, array $args, mixed $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        $this->setAdminGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Admin::class, 'id')],
            ],
        ];
    }

    public function type(): Type
    {
        return AdminType::nonNullType();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Admin
    {
        /** @var $model Admin */
        $model = $this->service->repo->getBy('id', $args['id']);

        if($model->isSuperAdmin()){
            throw new TranslatedException(__('exceptions.admin.cant_action_on_super_admin'));
        }

        if($model->isActive()){
            $this->service->deactivate($model);
        } else {
            $this->service->activate($model);
        }

        $model->refresh();

        return $model;
    }
}

