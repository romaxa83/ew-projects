<?php

namespace App\GraphQL\Mutations\Common\Tires;

use App\Dto\Tires\TireDto;
use App\GraphQL\InputTypes\Tires\TireInputType;
use App\GraphQL\Types\Tires\TireType;
use App\Models\Tires\Tire;
use App\Permissions\Tires\TireCreatePermission;
use App\Services\Tires\TireService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseTireCreateMutation extends BaseMutation
{
    public const NAME = 'tireCreate';
    public const PERMISSION = TireCreatePermission::KEY;

    public function __construct(protected TireService $service)
    {
        $this->setGuard();
    }

    abstract protected function setGuard(): void;

    public function type(): Type
    {
        return TireType::nonNullType();
    }

    public function args(): array
    {
        return [
            'tire' => [
                'type' => TireInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Tire
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Tire
    {
        return makeTransaction(
            fn() => $this->service->create(
                TireDto::byArgs($args['tire']),
                $this->user()
            )
        );
    }
}
