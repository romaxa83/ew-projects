<?php

namespace App\GraphQL\Mutations\Common\Dictionaries\TireModels;

use App\Dto\Dictionaries\TireModelDto;
use App\GraphQL\InputTypes\Dictionaries\TireModelInputType;
use App\GraphQL\Types\Dictionaries\TireModelType;
use App\Models\Dictionaries\TireModel;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\TireModelService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseTireModelCreateMutation extends BaseMutation
{
    public const NAME = 'tireModelCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

    public function __construct(protected TireModelService $service)
    {
        $this->setGuard();
    }

    abstract protected function setGuard(): void;

    public function type(): Type
    {
        return TireModelType::nonNullType();
    }

    public function args(): array
    {
        return [
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
            fn() => $this->service->create(
                TireModelDto::byArgs($args['tire_model']),
                $this->user()
            )
        );
    }
}
