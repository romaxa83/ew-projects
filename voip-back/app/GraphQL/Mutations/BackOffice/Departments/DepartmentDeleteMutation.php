<?php

namespace App\GraphQL\Mutations\BackOffice\Departments;

use App\GraphQL\Types\NonNullType;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;
use App\Models\Departments\Department;
use App\Permissions;
use App\Repositories\Departments\DepartmentRepository;
use App\Services\Departments\DepartmentService;
use Closure;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class DepartmentDeleteMutation extends BaseMutation
{
    public const NAME = 'DepartmentDelete';
    public const PERMISSION = Permissions\Departments\DeletePermission::KEY;

    public function __construct(
        protected DepartmentService $service,
        protected DepartmentRepository $repo,
        protected QueueService $queueService
    )
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null): bool
    {
        $this->setAdminGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Department::class, 'id')],
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
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
    ): bool
    {
        /** @var $model Department*/
        $model = $this->repo->getBy('id', $args['id'],
            withException: true,
            exceptionMessage: "Department not found by id [{$args['id']}]"
        );

        if($model->employeesCount() > 0){
            throw new TranslatedException(__('exceptions.department.cant_delete_exist_employee'));
        }

        if(config('app.enable_asterisk_kamailio')){
            if(!$this->queueService->remove($model)){
                throw new TranslatedException(__('exceptions.asterisk.queue.cant_delete'));
            }
        }

        return $this->service->delete($model);
    }
}
