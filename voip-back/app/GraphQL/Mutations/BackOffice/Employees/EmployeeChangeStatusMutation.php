<?php

namespace App\GraphQL\Mutations\BackOffice\Employees;

use App\Enums\Employees\Status;
use App\GraphQL\Types\Employees\EmployeeType;
use App\GraphQL\Types\Enums\Employees\StatusEnum;
use App\GraphQL\Types\NonNullType;
use App\IPTelephony\Events\QueueMember\QueueMemberPausedEvent;
use App\Models\Employees\Employee;
use App\PAMI\Client\Impl\ClientAMI;
use App\PAMI\Message\Action\QueueStatusAction;
use App\Permissions;
use App\Services\Employees\EmployeeService;
use Closure;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Error\AuthorizationError;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class EmployeeChangeStatusMutation extends BaseMutation
{
    public const NAME = 'EmployeeChangeStatus';
    public const PERMISSION = Permissions\Employees\ChangeStatusPermission::KEY;

    public function __construct(
        protected EmployeeService $service
    )
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null)
    : bool
    {
        $this->setAuthGuard();

        if($this->user() && $this->user()->isEmployee() && $this->user()->id != data_get($args, 'id')){
            throw new AuthorizationError(AuthorizationMessageEnum::NO_PERMISSION);
        }

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Employee::class, 'id')],
            ],
            'status' => StatusEnum::type(),
        ];
    }

    public function type(): Type
    {
        return EmployeeType::nonNullType();
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
    ): Employee
    {
        /** @var $model Employee */
        $model = $this->service->repo->getBy('id', $args['id']);

        $model = $this->service->changeStatus(
            $model,
            Status::fromValue($args['status'])
        );

        event(new QueueMemberPausedEvent(
            $model, $model->status->isPause()
        ));

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [
            'status' => ['required', Status::ruleIn()],
        ];
    }
}
