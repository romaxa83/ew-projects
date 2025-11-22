<?php


namespace App\GraphQL\Mutations\FrontOffice\Inspections;


use App\Dto\Inspections\InspectionDto;
use App\GraphQL\InputTypes\Inspection\InspectionInputType;
use App\GraphQL\InputTypes\Inspection\InspectionPhotosInputType;
use App\GraphQL\InputTypes\Inspection\InspectionTirePhotosInputType;
use App\GraphQL\Types\FileType;
use App\GraphQL\Types\Inspections\InspectionType;
use App\Models\Inspections\Inspection;
use App\Permissions\Inspections\InspectionCreatePermission;
use App\Rules\Inspections\InspectionLinkedRule;
use App\Rules\Inspections\InspectionTireSameRule;
use App\Services\Inspections\InspectionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class InspectionCreateMutation extends BaseMutation
{
    public const NAME = 'inspectionCreate';
    public const PERMISSION = InspectionCreatePermission::KEY;

    public function __construct(private InspectionService $service)
    {
        $this->setUserGuard();
    }

    public function args(): array
    {
        return [
            'inspection' => [
                'type' => InspectionInputType::nonNullType()
            ]
        ];
    }

    public function type(): Type
    {
        return InspectionType::nonNullType();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Inspection
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Inspection {

        return makeTransaction(
            fn() => $this->service->create(
                InspectionDto::byArgs($args['inspection']),
                $this->user()
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'inspection.tires.*.tire_id' => ['required', new InspectionTireSameRule($args)],
        ];
    }
}
