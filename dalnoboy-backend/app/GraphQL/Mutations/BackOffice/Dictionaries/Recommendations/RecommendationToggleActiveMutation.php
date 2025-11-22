<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\Recommendations;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\Recommendation;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\RecommendationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class RecommendationToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'recommendationToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private RecommendationService $service)
    {
        $this->setAdminGuard();
    }

    protected function getEntities(array $ids): Collection
    {
        return $this->service->getByIds($ids);
    }

    protected function action(iterable $entities): void
    {
        $this->service->toggleActiveMany($entities);
    }

    protected function getAdditionalRule(array $args): mixed
    {
        return Rule::exists(Recommendation::class, 'id');
    }
}
