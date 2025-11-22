<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireTypes;

use App\Dto\Dictionaries\TireTypeDto;
use App\GraphQL\InputTypes\Dictionaries\TireTypeInputType;
use App\GraphQL\Types\Dictionaries\TireTypeType;
use App\Models\Dictionaries\TireType;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\TireTypeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TireTypeCreateMutation extends BaseMutation
{
    public const NAME = 'tireTypeCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

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
            fn() => $this->service->create(
                TireTypeDto::byArgs($args['tire_type'])
            )
        );
    }
}
