<?php


namespace App\GraphQL\Mutations\BackOffice\Inspections;


use App\GraphQL\Types\Inspections\InspectionType;
use App\GraphQL\Types\NonNullType;
use App\Models\Inspections\Inspection;
use App\Models\Inspections\InspectionTire;
use App\Permissions\Inspections\InspectionUpdatePermission;
use App\Services\Inspections\InspectionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class InspectionTireUpdateMutation extends BaseMutation
{
    public const NAME = 'inspectionTireUpdate';
    public const PERMISSION = InspectionUpdatePermission::KEY;

    public function __construct(private InspectionService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'id' => [
                    'type' => NonNullType::id(),
                    'rules' => [
                        'required',
                        'int',
                        InspectionTire::ruleExists()
                    ]
                ],
                'ogp' => [
                    'type' => NonNullType::float(),
                ]
            ]
        );
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
            fn() => $this->service->updateInspectionTireOgp(
                InspectionTire::find($args['id']),
                $args['ogp']
            )->inspection
        );
    }
}
