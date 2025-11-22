<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\Regulations;

use App\Dto\Dictionaries\RegulationDto;
use App\GraphQL\InputTypes\Dictionaries\RegulationInputType;
use App\GraphQL\Types\Dictionaries\RegulationType;
use App\Models\Dictionaries\Regulation;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\RegulationService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class RegulationCreateMutation extends BaseMutation
{
    public const NAME = 'regulationCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

    public function __construct(private RegulationService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return RegulationType::nonNullType();
    }

    public function args(): array
    {
        return [
            'regulation' => [
                'type' => RegulationInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Regulation
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Regulation
    {
        return makeTransaction(
            fn() => $this->service->create(
                RegulationDto::byArgs($args['regulation'])
            )
        );
    }
}
