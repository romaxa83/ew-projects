<?php

namespace App\GraphQL\Mutations\BackOffice\Calls\Queue;

use App\GraphQL\Types\NonNullType;
use App\Models\Calls\Queue;
use App\Models\Employees\Employee;
use App\Permissions;
use App\Repositories\Employees\EmployeeRepository;
use App\Services\Calls\QueueService;
use Closure;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class QueueTransferToAgentMutation extends BaseMutation
{
    public const NAME = 'CallQueueTransferToAgent';
    public const PERMISSION = Permissions\Calls\Queue\TransferPermission::KEY;

    public function __construct(
        protected QueueService $service,
        protected EmployeeRepository $employeeRepository
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
            'employee_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Employee::class, 'id')],
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
        /** @var $employee Employee */
        $employee = $this->employeeRepository->getBy('id', $args['employee_id'], ['sip']);

        if(!$employee->sip){
            throw new TranslatedException(__('exceptions.employee.has_not_sip'));
        }

        if($this->user() instanceof Employee && !$this->user()->department->isActive()){
            throw new TranslatedException(__('exceptions.employee.can\'t_this_action'));
        }

        /** @var $queue Queue */
        $queue = $this->service->repo->getBy('id', $args['id']);

        try {

            return $this->service->transferToAgentOrDepartment($this->user(), $employee, $queue);
        } catch (\Exception $e) {
            throw new TranslatedException($e->getMessage());
        }
    }

    protected function rules(array $args = []): array
    {
        return [];
    }
}
