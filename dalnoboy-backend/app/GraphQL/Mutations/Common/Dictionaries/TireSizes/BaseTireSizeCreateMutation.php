<?php

namespace App\GraphQL\Mutations\Common\Dictionaries\TireSizes;

use App\Dto\Dictionaries\TireSizeDto;
use App\GraphQL\InputTypes\Dictionaries\TireSizeInputType;
use App\GraphQL\Types\Dictionaries\TireSizeType;
use App\Models\Dictionaries\TireSize;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\TireSizeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseTireSizeCreateMutation extends BaseMutation
{
    public const NAME = 'tireSizeCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

    public function __construct(protected TireSizeService $service)
    {
        $this->setGuard();
    }

    abstract protected function setGuard(): void;

    public function type(): Type
    {
        return TireSizeType::nonNullType();
    }

    public function args(): array
    {
        return [
            'tire_size' => [
                'type' => TireSizeInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return TireSize
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): TireSize
    {
        return makeTransaction(
            fn() => $this->service->create(
                TireSizeDto::byArgs($args['tire_size']),
                $this->user()
            )
        );
    }
}
