<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\RegulationDto;
use App\Models\Dictionaries\Regulation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RegulationService
{
    public function create(RegulationDto $dto): Regulation
    {
        return $this->editRegulation($dto, new Regulation());
    }

    public function update(RegulationDto $dto, Regulation $regulation): Regulation
    {
        return $this->editRegulation($dto, $regulation);
    }

    private function editRegulation(RegulationDto $dto, Regulation $regulation): Regulation
    {
        $regulation->active = $dto->isActive();
        $regulation->days = $dto->getDays();
        $regulation->distance = $dto->getDistance();
        $regulation->save();

        foreach (languages() as $language) {
            $translation = $dto->getTranslations()[$language->slug]
                ?? $dto->getTranslations()[defaultLanguage()->slug];

            $regulation->translates()
                ->updateOrCreate(
                    [
                        'language' => $language->slug,
                    ],
                    [
                        'title' => $translation->getTitle(),
                    ]
                );
        }

        return $regulation->refresh();
    }

    public function delete(Regulation $regulation): bool
    {
        //TODO chek relations
        return $regulation->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return Regulation::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return Regulation::whereKey($ids)->get();
    }

    /**
     * @param iterable<Regulation> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
