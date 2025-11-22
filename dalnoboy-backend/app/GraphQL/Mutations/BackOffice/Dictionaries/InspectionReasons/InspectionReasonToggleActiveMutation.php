<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\InspectionReasons;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\InspectionReason;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\InspectionReasonService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class InspectionReasonToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'inspectionReasonToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private InspectionReasonService $service)
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
        return Rule::exists(InspectionReason::class, 'id');
    }
}
