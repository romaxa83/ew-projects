<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\Recommendations;

use App\Dto\Dictionaries\RecommendationDto;
use App\GraphQL\InputTypes\Dictionaries\RecommendationInputType;
use App\GraphQL\Types\Dictionaries\RecommendationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\Recommendation;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\RecommendationService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class RecommendationUpdateMutation extends BaseMutation
{
    public const NAME = 'recommendationUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private RecommendationService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return RecommendationType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Recommendation::class, 'id')
                ]
            ],
            'recommendation' => [
                'type' => RecommendationInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Recommendation
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Recommendation
    {
        return makeTransaction(
            fn() => $this->service->update(
                RecommendationDto::byArgs($args['recommendation']),
                Recommendation::find($args['id'])
            )
        );
    }
}
