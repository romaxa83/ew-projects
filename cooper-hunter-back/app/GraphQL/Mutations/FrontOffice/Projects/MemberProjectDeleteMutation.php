<?php

namespace App\GraphQL\Mutations\FrontOffice\Projects;

use App\GraphQL\Types\NonNullType;
use App\Models\Projects\Project;
use App\Permissions\Projects\ProjectDeletePermission;
use App\Services\Projects\ProjectService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MemberProjectDeleteMutation extends BaseMutation
{
    public const NAME = 'memberProjectDelete';
    public const PERMISSION = ProjectDeletePermission::KEY;

    public function __construct(protected ProjectService $service)
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
                Project::query()->findOrFail($args['id'])
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
                    Rule::exists(Project::TABLE, 'id')
                        ->where(
                            'member_type',
                            $this->user()
                                ->getMorphType()
                        )
                        ->where('member_id', $this->user()->getId())
                ],
            ]
        );
    }
}
