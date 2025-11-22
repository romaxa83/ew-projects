<?php

namespace App\GraphQL\Mutations\FrontOffice\Inspections;

use App\GraphQL\InputTypes\Inspection\InspectionTirePhotosInputAsFileType;
use App\GraphQL\Types\Inspections\InspectionTireType;
use App\GraphQL\Types\NonNullType;
use App\Models\Inspections\Inspection;
use App\Models\Inspections\InspectionTire;
use App\Permissions\Inspections\InspectionCreatePermission;
use App\Services\Inspections\InspectionTireService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class InspectionTirePhotoUploadMutation extends BaseMutation
{
    public const NAME = 'inspectionTirePhotoUpload';
    public const PERMISSION = InspectionCreatePermission::KEY;

    public function __construct(private InspectionTireService $service)
    {
        $this->setUserGuard();
    }

    public function args(): array
    {
        return [
            'inspection_tire_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(InspectionTire::class, 'id')],
                'description' => "ID берем из этого типа - InspectionTireType"
            ],
            'photos' => [
                'type' => InspectionTirePhotosInputAsFileType::type(),
            ],
        ];
    }

    public function type(): Type
    {
        return InspectionTireType::nonNullType();
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
    ): InspectionTire
    {
        return makeTransaction(
            fn() => $this->service->upload(
                InspectionTire::find($args['inspection_tire_id']),
                $args['photos']
            )
        );
    }
}
