<?php

namespace App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects;

use App\Events\Commercial\DeleteCommercialProjectToOnec;
use App\Events\Commercial\SendCommercialProjectToOnec;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialProject;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectDeletePermission;
use App\Services\Commercial\CommercialProjectService;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\TechnicianCommercial;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialProjectDeleteMutation extends BaseMutation
{
    public const NAME = 'commercialProjectDelete';
    public const PERMISSION = CommercialProjectDeletePermission::KEY;

    public function __construct(private CommercialProjectService $service)
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
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
        $this->isTechnicianCommercial();

        $project = CommercialProject::find($args['id']);

        event(new DeleteCommercialProjectToOnec($project));

        return makeTransaction(
            fn() => $this->service->delete(
                CommercialProject::find($args['id']),
            ),
        );
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn(): array => [
                'id' => [
                    Rule::exists(CommercialProject::class, 'id')
                        ->where('member_type', $this->user()?->getMorphType())
                        ->where('member_id', $this->user()?->getId())
                ]
            ],
        );
    }
}
