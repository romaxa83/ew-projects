<?php

namespace App\GraphQL\Mutations\BackOffice\Calls\Queue;

use App\GraphQL\Types\NonNullType;
use App\Models\Calls\Queue;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Permissions;
use App\Repositories\Departments\DepartmentRepository;
use App\Services\Calls\QueueService;
use Closure;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class QueueTransferToDepartmentMutation extends BaseMutation
{
    public const NAME = 'CallQueueTransferToDepartment';
    public const PERMISSION = Permissions\Calls\Queue\TransferPermission::KEY;

    public function __construct(
        protected QueueService $service,
        protected DepartmentRepository $departmentRepository
    )
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null): bool
    {
        $this->setAuthGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Queue::class, 'id')],
            ],
            'department_id' => [
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
        /** @var $department Department */
        $department = $this->departmentRepository->getBy('id', $args['department_id']);

        /** @var $queue Queue */
        $queue = $this->service->repo->getBy('id', $args['id']);

        if($this->user() instanceof Employee && !$this->user()->department->isActive()){
            throw new TranslatedException(__('exceptions.employee.can\'t_this_action'));
        }

        try {
            $this->service->transferToAgentOrDepartment($this->user(), $department, $queue);

            return true;
        } catch (\Exception $e) {
            throw new TranslatedException($e->getMessage());
        }
    }

    protected function rules(array $args = []): array
    {
        return [];
    }
}
