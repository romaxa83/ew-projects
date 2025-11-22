<?php


namespace App\GraphQL\Mutations\Common\Inspections;


use App\Dto\Inspections\InspectionDto;
use App\GraphQL\InputTypes\Inspection\InspectionInputType;
use App\GraphQL\Types\Inspections\InspectionType;
use App\GraphQL\Types\NonNullType;
use App\Models\Inspections\Inspection;
use App\Permissions\Inspections\InspectionUpdatePermission;
use App\Services\Inspections\InspectionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseInspectionUpdateMutation extends BaseMutation
{
    public const NAME = 'inspectionUpdate';
    public const PERMISSION = InspectionUpdatePermission::KEY;

    public function __construct(private InspectionService $service)
    {
        $this->setMutationGuard();
    }

    abstract protected function setMutationGuard(): void;

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Inspection::ruleExists()
                        ->where('inspector_id', $this->getAuthUser()?->id)
                ]
            ],
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
            fn() => $this->service->update(
                InspectionDto::byArgs($args['inspection']),
                Inspection::find($args['id']),
                $this->user()
            )
        );
    }
}
