<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireSpecifications;

use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireSpecification;
use App\Permissions\Dictionaries\DictionaryDeletePermission;
use App\Services\Dictionaries\TireSpecificationService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TireSpecificationDeleteMutation extends BaseMutation
{
    public const NAME = 'tireSpecificationDelete';
    public const PERMISSION = DictionaryDeletePermission::KEY;

    public function __construct(private TireSpecificationService $service)
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
                    Rule::exists(TireSpecification::class, 'id')
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
        return $this->service->delete(TireSpecification::find($args['id']));
    }
}
