<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\Regulations;

use App\Dto\Dictionaries\RegulationDto;
use App\GraphQL\InputTypes\Dictionaries\RegulationInputType;
use App\GraphQL\Types\Dictionaries\RegulationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\Regulation;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\RegulationService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class RegulationUpdateMutation extends BaseMutation
{
    public const NAME = 'regulationUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

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
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Regulation::class, 'id')
                ]
            ],
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
            fn() => $this->service->update(
                RegulationDto::byArgs($args['regulation']),
                Regulation::find($args['id'])
            )
        );
    }
}
