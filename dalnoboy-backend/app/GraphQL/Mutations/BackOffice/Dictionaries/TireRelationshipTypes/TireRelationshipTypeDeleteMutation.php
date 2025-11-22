<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireRelationshipTypes;

use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireRelationshipType;
use App\Permissions\Dictionaries\DictionaryDeletePermission;
use App\Services\Dictionaries\TireRelationshipTypeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TireRelationshipTypeDeleteMutation extends BaseMutation
{
    public const NAME = 'tireRelationshipTypeDelete';
    public const PERMISSION = DictionaryDeletePermission::KEY;

    public function __construct(private TireRelationshipTypeService $service)
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
                    Rule::exists(TireRelationshipType::class, 'id')
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
        return $this->service->delete(TireRelationshipType::find($args['id']));
    }
}
