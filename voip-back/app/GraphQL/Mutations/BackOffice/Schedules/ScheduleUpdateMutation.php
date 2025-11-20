<?php

namespace App\GraphQL\Mutations\BackOffice\Schedules;

use App\Dto\Schedules\ScheduleDto;
use App\GraphQL\InputTypes\Schedules\AdditionDayInput;
use App\GraphQL\InputTypes\Schedules\DayInput;
use App\GraphQL\Types\Schedules\ScheduleType;
use App\Models\Schedules\Schedule;
use App\Permissions;
use App\Services\Schedules\ScheduleService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ScheduleUpdateMutation extends BaseMutation
{
    public const NAME = 'ScheduleUpdate';
    public const PERMISSION = Permissions\Schedules\UpdatePermission::KEY;

    public function __construct(
        protected ScheduleService $service
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
            'days' => Type::listOf(DayInput::type()),
            'additional_days' => Type::listOf(AdditionDayInput::type()),
        ];
    }

    public function type(): Type
    {
        return ScheduleType::list();
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
    ): Collection
    {
        $this->service->update(
            Schedule::first(),
            ScheduleDto::byArgs($args)
        );

        return $this->service->repo->getCollection(
            relation: ['days', 'additionalDays']
        );
    }

    protected function rules(array $args = []): array
    {
        return [];
    }
}
