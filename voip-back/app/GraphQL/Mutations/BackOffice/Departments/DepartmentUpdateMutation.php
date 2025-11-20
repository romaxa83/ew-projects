<?php

namespace App\GraphQL\Mutations\BackOffice\Departments;

use App\Dto\Departments\DepartmentDto;
use App\GraphQL\InputTypes\Departments\DepartmentInput;
use App\GraphQL\Types\Departments\DepartmentType;
use App\GraphQL\Types\NonNullType;
use App\IPTelephony\Events\Queue\QueueUpdateOrCreateEvent;
use App\Models\Departments\Department;
use App\Permissions;
use App\Services\Departments\DepartmentService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class DepartmentUpdateMutation extends BaseMutation
{
    public const NAME = 'DepartmentUpdate';
    public const PERMISSION = Permissions\Departments\UpdatePermission::KEY;

    public function __construct(
        protected DepartmentService $service
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
            'input' => DepartmentInput::nonNullType(),
        ];
    }

    public function type(): Type
    {
        return DepartmentType::nonNullType();
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
    ): Department
    {
        /** @var $model Department */
        $model = $this->service->repo->getBy('id', $args['id']);

        $model = $this->service->update(
            $model,
            DepartmentDto::byArgs($args['input'])
        );

        event(new QueueUpdateOrCreateEvent($model));

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.name' => [
                'required',
                'string',
                Rule::unique(Department::TABLE, 'name')->ignore($args['id'])
            ],
        ];
    }
}
