<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireHeights;

use App\Dto\Dictionaries\TireHeightDto;
use App\GraphQL\InputTypes\Dictionaries\TireHeightInputType;
use App\GraphQL\Types\Dictionaries\TireHeightType;
use App\Models\Dictionaries\TireHeight;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\TireHeightService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TireHeightCreateMutation extends BaseMutation
{
    public const NAME = 'tireHeightCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

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
            fn() => $this->service->create(
                TireHeightDto::byArgs($args['tire_height'])
            )
        );
    }
}
