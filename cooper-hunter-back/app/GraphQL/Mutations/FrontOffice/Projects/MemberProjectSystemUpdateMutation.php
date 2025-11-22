<?php

namespace App\GraphQL\Mutations\FrontOffice\Projects;

use App\Dto\Projects\ProjectSystemDto;
use App\GraphQL\InputTypes\Projects\Systems\ProjectSystemUpdateInput;
use App\GraphQL\Types\Projects\ProjectSystemType;
use App\Models\Projects\System;
use App\Permissions\Projects\ProjectUpdatePermission;
use App\Rules\Catalog\UnitSerialNumberRule;
use App\Rules\Catalog\UnitSerialNumberUniqueRule;
use App\Rules\Projects\SystemBelongsToMemberRule;
use App\Services\Projects\SystemService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MemberProjectSystemUpdateMutation extends BaseMutation
{
    public const NAME = 'memberProjectSystemUpdate';
    public const PERMISSION = ProjectUpdatePermission::KEY;

    public function __construct(protected SystemService $service)
    {
        $this->setMemberGuard();
    }

    public function args(): array
    {
        return [
            'system' => [
                'type' => ProjectSystemUpdateInput::nonNullType(),
            ],
        ];
    }

    public function type(): Type
    {
        return ProjectSystemType::type();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): System
    {
        return makeTransaction(
            fn() => $this->service->updateUsingDto(
                ProjectSystemDto::byArgs($args['system'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn() => [
                'system.id' => ['required', 'int', new SystemBelongsToMemberRule($this->user())],
                'system.units.*' => [
                    'required',
                    'array',
                    new UnitSerialNumberRule(),
                    (new UnitSerialNumberUniqueRule())
                        ->ignoreSystem($args['system']['id'])
                        ->uniqueForMember($this->user())
                ],
                'system.units.*.serial_number' => ['sometimes', 'distinct'],
            ]
        );
    }
}
