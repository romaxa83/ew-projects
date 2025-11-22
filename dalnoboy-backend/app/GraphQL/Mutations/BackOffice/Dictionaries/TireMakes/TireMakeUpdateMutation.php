<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireMakes;

use App\Dto\Dictionaries\TireMakeDto;
use App\GraphQL\InputTypes\Dictionaries\TireMakeInputType;
use App\GraphQL\Types\Dictionaries\TireMakeType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireMake;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireMakeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TireMakeUpdateMutation extends BaseMutation
{
    public const NAME = 'tireMakeUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireMakeService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return TireMakeType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(TireMake::class, 'id')
                ]
            ],
            'tire_make' => [
                'type' => TireMakeInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return TireMake
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): TireMake
    {
        return makeTransaction(
            fn() => $this->service->update(
                TireMakeDto::byArgs($args['tire_make']),
                TireMake::find($args['id'])
            )
        );
    }
}
