<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireHeights;

use App\Dto\Dictionaries\TireHeightDto;
use App\GraphQL\InputTypes\Dictionaries\TireHeightInputType;
use App\GraphQL\Types\Dictionaries\TireHeightType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireHeight;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireHeightService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TireHeightUpdateMutation extends BaseMutation
{
    public const NAME = 'tireHeightUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireHeightService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return TireHeightType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(TireHeight::class, 'id')
                ]
            ],
            'tire_height' => [
                'type' => TireHeightInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return TireHeight
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): TireHeight
    {
        return makeTransaction(
            fn() => $this->service->update(
                TireHeightDto::byArgs($args['tire_height']),
                TireHeight::find($args['id'])
            )
        );
    }
}
