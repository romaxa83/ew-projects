<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireSizes;

use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireSize;
use App\Permissions\Dictionaries\DictionaryDeletePermission;
use App\Services\Dictionaries\TireSizeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TireSizeDeleteMutation extends BaseMutation
{
    public const NAME = 'tireSizeDelete';
    public const PERMISSION = DictionaryDeletePermission::KEY;

    public function __construct(private TireSizeService $service)
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
                    Rule::exists(TireSize::class, 'id')
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
        return $this->service->delete(TireSize::find($args['id']));
    }
}
