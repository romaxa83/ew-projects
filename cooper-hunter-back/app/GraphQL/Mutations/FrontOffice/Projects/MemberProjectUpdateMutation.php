<?php

namespace App\GraphQL\Mutations\FrontOffice\Projects;

use App\Dto\Projects\ProjectDto;
use App\GraphQL\InputTypes\Projects\ProjectUpdateInput;
use App\Models\Projects\Project;
use App\Permissions\Projects\ProjectUpdatePermission;
use App\Rules\Catalog\SystemUnitSerialNumberUniqueRule;
use App\Rules\Catalog\UnitSerialNumberRule;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MemberProjectUpdateMutation extends BaseProjectMutation
{
    public const NAME = 'memberProjectUpdate';
    public const PERMISSION = ProjectUpdatePermission::KEY;

    public function args(): array
    {
        return [
            'project' => ProjectUpdateInput::nonNullType(),
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
        $project = Project::query()
            ->findOrFail(Arr::get($args, 'project.id'));

        return makeTransaction(
            fn() => $this->service->update(
                $project,
                ProjectDto::byArgs($args['project']),
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn(): array => [
                'project.id' => [
                    'required',
                    'integer',
                    Rule::exists(Project::TABLE, 'id')
                        ->where(
                            'member_type',
                            $this->user()?->getMorphType()
                        )
                        ->where('member_id', $this->user()?->getId())
                ],
                'project.name' => ['required', 'string'],
                'project.systems' => [
                    'sometimes',
                    'nullable',
                    'array',
                    (new SystemUnitSerialNumberUniqueRule())->uniqueForMember($this->user())
                ],
                'project.systems.*.units' => ['sometimes', 'nullable', 'array'],
                'project.systems.*.units.*' => [
                    'required',
                    'array',
                    new UnitSerialNumberRule(),
                ],
                'project.systems.*.units.*.serial_number' => ['sometimes', 'distinct'],
            ]
        );
    }
}
