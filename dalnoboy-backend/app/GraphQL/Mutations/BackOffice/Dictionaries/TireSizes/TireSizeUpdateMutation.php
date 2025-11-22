<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireSizes;

use App\Dto\Dictionaries\TireSizeDto;
use App\GraphQL\InputTypes\Dictionaries\TireSizeInputType;
use App\GraphQL\Types\Dictionaries\TireSizeType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireSize;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireSizeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TireSizeUpdateMutation extends BaseMutation
{
    public const NAME = 'tireSizeUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireSizeService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return TireSizeType::nonNullType();
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
            ],
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
            fn() => $this->service->update(
                TireSizeDto::byArgs($args['tire_size']),
                TireSize::find($args['id'])
            )
        );
    }
}
