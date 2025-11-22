<?php

namespace App\GraphQL\Mutations\FrontOffice\Projects;

use App\GraphQL\Types\NonNullType;
use App\Models\Projects\System;
use App\Permissions\Projects\ProjectDeletePermission;
use App\Rules\Projects\SystemBelongsToMemberRule;
use App\Services\Projects\SystemService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MemberProjectSystemDeleteMutation extends BaseMutation
{
    public const NAME = 'memberProjectSystemDelete';
    public const PERMISSION = ProjectDeletePermission::KEY;

    public function __construct(protected SystemService $service)
    {
        $this->setMemberGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
        ];
    }

    /** @throws Throwable */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(
                System::query()->findOrFail($args['id'])
            ),
        );
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn() => [
                'id' => [
                    'required',
                    'integer',
                    new SystemBelongsToMemberRule($this->user())
                ],
            ]
        );
    }
}
