<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireRelationshipTypes;

use App\Dto\Dictionaries\TireRelationshipTypeDto;
use App\GraphQL\InputTypes\Dictionaries\TireRelationshipTypeInputType;
use App\GraphQL\Types\Dictionaries\TireRelationshipTypeType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireRelationshipType;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireRelationshipTypeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TireRelationshipTypeUpdateMutation extends BaseMutation
{
    public const NAME = 'tireRelationshipTypeUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireRelationshipTypeService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return TireRelationshipTypeType::nonNullType();
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
            ],
            'tire_relationship_type' => [
                'type' => TireRelationshipTypeInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return TireRelationshipType
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): TireRelationshipType
    {
        return makeTransaction(
            fn() => $this->service->update(
                TireRelationshipTypeDto::byArgs($args['tire_relationship_type']),
                TireRelationshipType::find($args['id'])
            )
        );
    }
}
