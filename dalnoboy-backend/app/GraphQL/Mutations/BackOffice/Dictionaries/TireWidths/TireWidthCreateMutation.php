<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireWidths;

use App\Dto\Dictionaries\TireWidthDto;
use App\GraphQL\InputTypes\Dictionaries\TireWidthInputType;
use App\GraphQL\Types\Dictionaries\TireWidthType;
use App\Models\Dictionaries\TireWidth;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\TireWidthService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TireWidthCreateMutation extends BaseMutation
{
    public const NAME = 'tireWidthCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

    public function __construct(private TireWidthService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return TireWidthType::nonNullType();
    }

    public function args(): array
    {
        return [
            'tire_width' => [
                'type' => TireWidthInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return TireWidth
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): TireWidth {
        return makeTransaction(
            fn() => $this->service->create(
                TireWidthDto::byArgs($args['tire_width'])
            )
        );
    }
}
