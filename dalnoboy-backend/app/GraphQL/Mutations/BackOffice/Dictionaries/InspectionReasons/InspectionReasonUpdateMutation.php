<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\InspectionReasons;

use App\Dto\Dictionaries\InspectionReasonDto;
use App\GraphQL\InputTypes\Dictionaries\InspectionReasonInputType;
use App\GraphQL\Types\Dictionaries\InspectionReasonType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\InspectionReason;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\InspectionReasonService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class InspectionReasonUpdateMutation extends BaseMutation
{
    public const NAME = 'inspectionReasonUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private InspectionReasonService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return InspectionReasonType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(InspectionReason::class, 'id')
                ]
            ],
            'inspection_reason' => [
                'type' => InspectionReasonInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return InspectionReason
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): InspectionReason
    {
        return makeTransaction(
            fn() => $this->service->update(
                InspectionReasonDto::byArgs($args['inspection_reason']),
                InspectionReason::find($args['id'])
            )
        );
    }
}
