<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\InspectionReasons;

use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\InspectionReason;
use App\Permissions\Dictionaries\DictionaryDeletePermission;
use App\Services\Dictionaries\InspectionReasonService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class InspectionReasonDeleteMutation extends BaseMutation
{
    public const NAME = 'inspectionReasonDelete';
    public const PERMISSION = DictionaryDeletePermission::KEY;

    public function __construct(private InspectionReasonService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return NonNullType::boolean();
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
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return $this->service->delete(InspectionReason::find($args['id']));
    }
}
