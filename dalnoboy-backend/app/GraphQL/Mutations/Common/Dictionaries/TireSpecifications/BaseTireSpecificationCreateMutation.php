<?php

namespace App\GraphQL\Mutations\Common\Dictionaries\TireSpecifications;

use App\Dto\Dictionaries\TireSpecificationDto;
use App\GraphQL\InputTypes\Dictionaries\TireSpecificationInputType;
use App\GraphQL\Types\Dictionaries\TireSpecificationType;
use App\Models\Dictionaries\TireSpecification;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\TireSpecificationService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseTireSpecificationCreateMutation extends BaseMutation
{
    public const NAME = 'tireSpecificationCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

    public function __construct(protected TireSpecificationService $service)
    {
        $this->setGuard();
    }

    abstract protected function setGuard(): void;

    public function type(): Type
    {
        return TireSpecificationType::nonNullType();
    }

    public function args(): array
    {
        return [
            'tire_specification' => [
                'type' => TireSpecificationInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return TireSpecification
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): TireSpecification
    {
        return makeTransaction(
            fn() => $this->service->create(
                TireSpecificationDto::byArgs($args['tire_specification'])
            )
        );
    }
}
