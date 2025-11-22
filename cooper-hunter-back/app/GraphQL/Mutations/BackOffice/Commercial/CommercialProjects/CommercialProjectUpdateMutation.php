<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\CommercialProjects;

use App\Dto\Commercial\CommercialProjectDto;
use App\Events\Commercial\SendCommercialProjectToOnec;
use App\GraphQL\InputTypes\Commercial\CommercialProjectAdminInput;
use App\GraphQL\Types\Commercial\CommercialProjectType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialProject;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectUpdatePermission;
use App\Repositories\Locations\CountryRepository;
use App\Services\Commercial\CommercialProjectService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialProjectUpdateMutation extends BaseMutation
{
    public const NAME = 'commercialProjectUpdate';

    public const PERMISSION = CommercialProjectUpdatePermission::KEY;

    public function __construct(
        private CommercialProjectService $service,
        protected CountryRepository $countryRepository
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(CommercialProject::class, 'id')],
            ],
            'input' => [
                'type' => CommercialProjectAdminInput::nonNullType()
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
        $dto = CommercialProjectDto::byArgs($args['input']);

        $project = makeTransaction(
            fn(): CommercialProject => $this->service->update(
                CommercialProject::find($args['id']),
                $dto
            )
        );

        event(new SendCommercialProjectToOnec($project));

        return $project;
    }
}
