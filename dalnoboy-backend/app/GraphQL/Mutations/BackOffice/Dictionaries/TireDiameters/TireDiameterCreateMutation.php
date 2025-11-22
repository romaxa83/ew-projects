<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireDiameters;

use App\Dto\Dictionaries\TireDiameterDto;
use App\GraphQL\InputTypes\Dictionaries\TireDiameterInputType;
use App\GraphQL\Types\Dictionaries\TireDiameterType;
use App\Models\Dictionaries\TireDiameter;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\TireDiameterService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TireDiameterCreateMutation extends BaseMutation
{
    public const NAME = 'tireDiameterCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

    public function __construct(private TireDiameterService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return TireDiameterType::nonNullType();
    }

    public function args(): array
    {
        return [
            'tire_diameter' => [
                'type' => TireDiameterInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return TireDiameter
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): TireDiameter
    {
        return makeTransaction(
            fn() => $this->service->create(
                TireDiameterDto::byArgs($args['tire_diameter'])
            )
        );
    }
}
