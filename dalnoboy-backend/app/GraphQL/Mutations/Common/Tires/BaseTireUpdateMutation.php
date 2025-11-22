<?php

namespace App\GraphQL\Mutations\Common\Tires;

use App\Dto\Tires\TireDto;
use App\GraphQL\InputTypes\Tires\TireInputType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Tires\TireType;
use App\Models\Tires\Tire;
use App\Permissions\Tires\TireUpdatePermission;
use App\Services\Tires\TireService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseTireUpdateMutation extends BaseMutation
{
    public const NAME = 'tireUpdate';
    public const PERMISSION = TireUpdatePermission::KEY;

    public function __construct(private TireService $service)
    {
        $this->setMutationGuard();
    }

    abstract protected function setMutationGuard(): void;

    public function type(): Type
    {
        return TireType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Tire::class, 'id')
                ]
            ],
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
            fn() => $this->service->update(
                TireDto::byArgs($args['tire']),
                Tire::find($args['id'])
            )
        );
    }
}
