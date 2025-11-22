<?php

namespace App\GraphQL\Mutations\Common\Dictionaries\TireMakes;

use App\Dto\Dictionaries\TireMakeDto;
use App\GraphQL\InputTypes\Dictionaries\TireMakeInputType;
use App\GraphQL\Types\Dictionaries\TireMakeType;
use App\Models\Dictionaries\TireMake;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\TireMakeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseTireMakeCreateMutation extends BaseMutation
{
    public const NAME = 'tireMakeCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

    public function __construct(protected TireMakeService $service)
    {
        $this->setGuard();
    }

    abstract protected function setGuard(): void;

    public function type(): Type
    {
        return TireMakeType::nonNullType();
    }

    public function args(): array
    {
        return [
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
            fn() => $this->service->create(
                TireMakeDto::byArgs($args['tire_make']),
                $this->user()
            )
        );
    }
}
