<?php

namespace App\GraphQL\Mutations\BackOffice\Employees;

use App\GraphQL\Types\NonNullType;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;
use App\IPTelephony\Services\Storage\Kamailio\SubscriberService;
use App\Models\Employees\Employee;
use App\Permissions;
use App\Repositories\Employees\EmployeeRepository;
use App\Services\Employees\EmployeeService;
use Closure;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class EmployeeDeleteMutation extends BaseMutation
{
    public const NAME = 'EmployeeDelete';
    public const PERMISSION = Permissions\Employees\DeletePermission::KEY;

    public function __construct(
        protected EmployeeService $service,
        protected EmployeeRepository $repo,
        protected SubscriberService $subscriberService,
        protected QueueMemberService $queueMemberService
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
        $this->setAdminGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
            'id' => ['type' => NonNullType::id()],
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
        /** @var $model Employee*/
        $model = $this->repo->getBy('id', $args['id'],
            withException: true,
            exceptionMessage: "Employee not found by id [{$args['id']}]"
        );

        if(config('app.enable_asterisk_kamailio')){
            if(!$this->subscriberService->remove($model)){
                throw new TranslatedException(__('exceptions.kamailio.cant_delete_subscriber'));
            }
            if(!$this->queueMemberService->remove($model)){
                throw new TranslatedException(__('exceptions.asterisk.queue_member.cant_delete'));
            }
        }

        return makeTransaction(
            fn(): bool => $this->service->remove($model)
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'int', Rule::exists(Employee::class, 'id')],
        ];
    }
}
