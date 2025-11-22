<?php


namespace App\GraphQL\Mutations\FrontOffice\Inspections;


use App\GraphQL\InputTypes\Inspection\InspectionUnLinkedInputType;
use App\GraphQL\Types\Inspections\InspectionType;
use App\Models\Inspections\Inspection;
use App\Permissions\Inspections\InspectionUpdatePermission;
use App\Services\Inspections\InspectionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class InspectionUnLinkedMutation extends BaseMutation
{
    public const NAME = 'inspectionUnLinked';
    public const PERMISSION = InspectionUpdatePermission::KEY;
    public const DESCRIPTION = 'Unlinked trailer vehicle inspection from main vehicle inspection. Return list with two inspections (Or one if of them not user\'s inspection).';

    public function __construct(private InspectionService $service)
    {
        $this->setUserGuard();
    }

    public function args(): array
    {
        return [
            'unlinked_inspections' => [
                'type' => InspectionUnLinkedInputType::nonNullType()
            ]
        ];
    }

    public function type(): Type
    {
        return InspectionType::nonNullList();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Collection
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return makeTransaction(
            fn() => $this->service->unlinked(
                Inspection::find($args['unlinked_inspections']['inspection_id']),
                $this->user()
            )
        );
    }
}
