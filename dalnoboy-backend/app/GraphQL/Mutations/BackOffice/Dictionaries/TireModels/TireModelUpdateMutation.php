<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireModels;

use App\Dto\Dictionaries\TireModelDto;
use App\GraphQL\InputTypes\Dictionaries\TireModelInputType;
use App\GraphQL\Types\Dictionaries\TireModelType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireModel;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireModelService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TireModelUpdateMutation extends BaseMutation
{
    public const NAME = 'tireModelUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireModelService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return TireModelType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(TireModel::class, 'id')
                ]
            ],
            'tire_model' => [
                'type' => TireModelInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return TireModel
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): TireModel
    {
        return makeTransaction(
            fn() => $this->service->update(
                TireModelDto::byArgs($args['tire_model']),
                TireModel::find($args['id'])
            )
        );
    }
}
