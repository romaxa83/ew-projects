<?php

namespace App\GraphQL\Mutations\BackOffice\Utilities\Sorting;

use App\Enums\Sorting\SortingModelsEnum;
use App\GraphQL\Types\Enums\Sorting\SortingModelsTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\Services\Utilities\SortModelsService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SortModelsMutation extends BaseMutation
{
    public const NAME = 'sortModels';

    public function __construct(protected SortModelsService $service)
    {
        $this->setAdminGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->authCheck();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'model' => [
                'type' => SortingModelsTypeEnum::nonNullType(),
            ],
            'data' => [
                'type' => NonNullType::listOf(NonNullType::id()),
                'description' => 'Список id моделей, сортировка будет выполнена по последовательности id в списке',
            ],
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $model = SortingModelsEnum::getValue($args['model']);

        $object = new $model();

        return makeTransaction(
            fn() => $this->service->sort($object, $args['data'])
        );
    }
}
