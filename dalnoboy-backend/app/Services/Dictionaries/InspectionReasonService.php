<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\InspectionReasonDto;
use App\Models\Dictionaries\InspectionReason;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InspectionReasonService
{
    public function create(InspectionReasonDto $dto): InspectionReason
    {
        return $this->editInspectionReason($dto, new InspectionReason());
    }

    public function update(InspectionReasonDto $dto, InspectionReason $inspectionReason): InspectionReason
    {
        return $this->editInspectionReason($dto, $inspectionReason);
    }

    private function editInspectionReason(InspectionReasonDto $dto, InspectionReason $inspectionReason): InspectionReason
    {
        $inspectionReason->active = $dto->isActive();
        $inspectionReason->need_description = $dto->isNeedDescription();
        $inspectionReason->save();

        foreach ($dto->getTranslations() ?? [] as $translation) {
            $inspectionReason->translates()
                ->updateOrCreate(
                    [
                        'language' => $translation->getLanguage(),
                    ],
                    [
                        'title' => $translation->getTitle(),
                    ]
                );
        }

        return $inspectionReason->refresh();
    }

    public function delete(InspectionReason $inspectionReason): bool
    {
        //TODO chek relations
        return $inspectionReason->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return InspectionReason::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return InspectionReason::whereKey($ids)->get();
    }

    /**
     * @param iterable<InspectionReason> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
