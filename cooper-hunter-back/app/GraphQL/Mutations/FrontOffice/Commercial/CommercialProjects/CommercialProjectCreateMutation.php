<?php

namespace App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects;

use App\Dto\Commercial\CommercialProjectDto;
use App\Enums\Formats\DatetimeEnum;
use App\Events\Commercial\SendCommercialProjectToOnec;
use App\GraphQL\InputTypes\Commercial\CommercialProjectInput;
use App\GraphQL\Types\Commercial\CommercialProjectType;
use App\Models\Commercial\CommercialProject;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectCreatePermission;
use App\Repositories\Locations\CountryRepository;
use App\Services\Commercial\CommercialProjectService;
use App\Services\OneC\RequestService;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\TechnicianCommercial;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialProjectCreateMutation extends BaseMutation
{
    public const NAME = 'commercialProjectCreate';
    public const PERMISSION = CommercialProjectCreatePermission::KEY;

    public function __construct(
        private CommercialProjectService $service,
        protected CountryRepository $countryRepository
    )
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [
            'input' => [
                'type' => CommercialProjectInput::nonNullType(),
            ]
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
            fn() => $this->service->create(
                $this->user(),
                CommercialProjectDto::byArgs($args['input'])
            )
        );

        event(new SendCommercialProjectToOnec($project));

        return $project;
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.estimate_start_date' => [
                'required',
                DatetimeEnum::DATE_RULE,
                'before_or_equal:input.estimate_end_date',
            ],
            'input.estimate_end_date' => [
                'required',
                DatetimeEnum::DATE_RULE,
                'after_or_equal:input.estimate_start_date',
            ],
        ];
    }
}
