<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireSpecifications;

use App\Dto\Dictionaries\TireSpecificationDto;
use App\GraphQL\InputTypes\Dictionaries\TireSpecificationInputType;
use App\GraphQL\Types\Dictionaries\TireSpecificationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireSpecification;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireSpecificationService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TireSpecificationUpdateMutation extends BaseMutation
{
    public const NAME = 'tireSpecificationUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireSpecificationService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return TireSpecificationType::nonNullType();
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
            ],
            'tire_specification' => [
                'type' => TireSpecificationInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return TireSpecification
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): TireSpecification
    {
        return makeTransaction(
            fn() => $this->service->update(
                TireSpecificationDto::byArgs($args['tire_specification']),
                TireSpecification::find($args['id'])
            )
        );
    }
}
