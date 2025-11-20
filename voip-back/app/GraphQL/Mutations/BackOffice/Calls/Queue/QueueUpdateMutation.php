<?php

namespace App\GraphQL\Mutations\BackOffice\Calls\Queue;

use App\Dto\Calls\QueueDto;
use App\GraphQL\InputTypes\Calls\QueueInput;
use App\GraphQL\Types\Calls\Queue\QueueType;
use App\GraphQL\Types\NonNullType;
use App\Models\Calls\Queue;
use App\Permissions;
use App\Services\Calls\QueueService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class QueueUpdateMutation extends BaseMutation
{
    public const NAME = 'CallQueueUpdate';
    public const PERMISSION = Permissions\Calls\Queue\UpdatePermission::KEY;

    public function __construct(
        protected QueueService $service
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
            'input' => QueueInput::nonNullType(),
        ];
    }

    public function type(): Type
    {
        return QueueType::nonNullType();
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
    ): Queue
    {
        /** @var $model Queue */
        $model = $this->service->repo->getBy('id', $args['id']);

        return $this->service->update(
            $model,
            QueueDto::byArgs($args['input'])
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.comment' => ['nullable', 'string', 'max:1000'],
            'input.from_name' => ['nullable', 'string', 'max:255'],
            'input.serial_number' => ['nullable', 'string', 'max:255'],
            'input.case_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
