<?php

namespace App\GraphQL\Mutations\FrontOffice\Projects;

use App\Dto\Projects\ProjectDto;
use App\GraphQL\InputTypes\Projects\ProjectCreateInput;
use App\Models\Projects\Project;
use App\Models\Technicians\Technician;
use App\Permissions\Projects\ProjectCreatePermission;
use App\Rules\Catalog\UnitSerialNumberRule;
use App\Rules\Catalog\UnitSerialNumberUniqueRule;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MemberProjectCreateMutation extends BaseProjectMutation
{
    public const NAME = 'memberProjectCreate';
    public const PERMISSION = ProjectCreatePermission::KEY;

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->can(self::PERMISSION) && $this->can('isActive', Technician::class);
    }

    public function args(): array
    {
        return [
            'project' => ProjectCreateInput::nonNullType()
        ];
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
    ): Project {
        return makeTransaction(
            fn() => $this->service->create(
                ProjectDto::byArgs($args['project']),
                $this->user(),
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn() => [
                'project.name' => ['required', 'string'],
                'project.systems' => ['nullable', 'array'],
                'project.systems.*.units' => ['nullable', 'array'],
                'project.systems.*.units.*' => [
                    'required',
                    'array',
                    new UnitSerialNumberRule(),
                    (new UnitSerialNumberUniqueRule())
                        ->uniqueForMember($this->user())
                ],
                'project.systems.*.units.*.serial_number' => ['sometimes', 'distinct'],
            ]
        );
    }
}
