<?php

namespace App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects;

use App\Events\Commercial\SendCommercialProjectToOnec;
use App\GraphQL\Types\Commercial\CommercialProjectType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialProject;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectUpdatePermission;
use App\Services\Commercial\CommercialProjectService;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\TechnicianCommercial;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialProjectUpdateMutation extends BaseMutation
{
    public const NAME = 'commercialProjectUpdate';
    public const PERMISSION = CommercialProjectUpdatePermission::KEY;

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
            'name' => [
                'type' => NonNullType::string(),
                'rules' => ['string', 'max:255'],
            ],
        ];
    }

    public function type(): Type
    {
        return CommercialProjectType::nonNullType();
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
    ): CommercialProject
    {
        $this->isTechnicianCommercial();

        $project = makeTransaction(
            fn() => $this->service->updateName(
                CommercialProject::find($args['id']),
                $args['name'],
            ),
        );

        event(new SendCommercialProjectToOnec($project));

        return $project;
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
