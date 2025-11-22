<?php


namespace App\Services\SupportRequests;


use App\Contracts\Roles\HasGuardUser;
use App\Exceptions\SupportRequests\SubjectUsedInRequestsException;
use App\Models\BaseHasTranslation;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Services\BaseCrudDictionaryService;
use Illuminate\Database\Eloquent\Collection;

class SupportRequestSubjectService extends BaseCrudDictionaryService
{
    protected function getModel(): string
    {
        return SupportRequestSubject::class;
    }

    /**
     * @param SupportRequestSubject|BaseHasTranslation $model
     * @throws SubjectUsedInRequestsException
     */
    protected function checkOffModel(SupportRequestSubject|BaseHasTranslation $model): void
    {
        if (!$model->supportRequests()
            ->exists()) {
            return;
        }

        throw new SubjectUsedInRequestsException();
    }

    public function getList(array $args, HasGuardUser $authUser): ?Collection
    {
        return SupportRequestSubject::forGuard($authUser)
            ->filter($args)
            ->latest('sort')
            ->get();
    }
}
