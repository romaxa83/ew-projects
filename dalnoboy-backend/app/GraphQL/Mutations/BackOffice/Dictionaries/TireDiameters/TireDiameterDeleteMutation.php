<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireDiameters;

use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireDiameter;
use App\Permissions\Dictionaries\DictionaryDeletePermission;
use App\Services\Dictionaries\TireDiameterService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TireDiameterDeleteMutation extends BaseMutation
{
    public const NAME = 'tireDiameterDelete';
    public const PERMISSION = DictionaryDeletePermission::KEY;

    public function __construct(private TireDiameterService $service)
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
                    Rule::exists(TireDiameter::class, 'id')
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
        return $this->service->delete(TireDiameter::find($args['id']));
    }
}
