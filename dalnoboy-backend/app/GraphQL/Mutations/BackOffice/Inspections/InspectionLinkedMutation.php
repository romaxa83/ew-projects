<?php


namespace App\GraphQL\Mutations\BackOffice\Inspections;


use App\GraphQL\InputTypes\Inspection\InspectionLinkedInputType;
use App\GraphQL\Types\Inspections\InspectionType;
use App\Models\Inspections\Inspection;
use App\Permissions\Inspections\InspectionUpdatePermission;
use App\Services\Inspections\InspectionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class InspectionLinkedMutation extends BaseMutation
{
    public const NAME = 'inspectionLinked';
    public const PERMISSION = InspectionUpdatePermission::KEY;
    public const DESCRIPTION = 'Linked trailer vehicle inspection to main vehicle inspection. Return main vehicle inspection.';

    public function __construct(private InspectionService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'linked_inspections' => [
                'type' => InspectionLinkedInputType::nonNullType()
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
            fn() => $this->service->linked(
                Inspection::find($args['linked_inspections']['main_inspection_id']),
                Inspection::find($args['linked_inspections']['trailer_inspection_id']),
                $this->user()
            )
        );
    }
}
