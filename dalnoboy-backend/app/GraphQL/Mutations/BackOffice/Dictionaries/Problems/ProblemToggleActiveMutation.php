<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\Problems;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\Problem;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\ProblemService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class ProblemToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'problemToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private ProblemService $service)
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
        return Rule::exists(Problem::class, 'id');
    }
}
