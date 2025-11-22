<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireTypes;

use App\Dto\Dictionaries\TireTypeDto;
use App\GraphQL\InputTypes\Dictionaries\TireTypeInputType;
use App\GraphQL\Types\Dictionaries\TireTypeType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireType;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireTypeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TireTypeUpdateMutation extends BaseMutation
{
    public const NAME = 'tireTypeUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireTypeService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return TireTypeType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(TireType::class, 'id')
                ]
            ],
            'tire_type' => [
                'type' => TireTypeInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return TireType
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): TireType
    {
        return makeTransaction(
            fn() => $this->service->update(
                TireTypeDto::byArgs($args['tire_type']),
                TireType::find($args['id'])
            )
        );
    }
}
