<?php

namespace App\GraphQL\Mutations\BackOffice\Tires;

use App\GraphQL\Types\NonNullType;
use App\Models\Tires\Tire;
use App\Permissions\Tires\TireDeletePermission;
use App\Services\Tires\TireService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TireDeleteMutation extends BaseMutation
{
    public const NAME = 'tireDelete';
    public const PERMISSION = TireDeletePermission::KEY;

    public function __construct(private TireService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return NonNullType::boolean();
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
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return $this->service->delete(Tire::find($args['id']));
    }
}
