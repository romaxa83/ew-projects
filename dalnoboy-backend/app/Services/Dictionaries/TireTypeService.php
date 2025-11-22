<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\TireTypeDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Dictionaries\TireType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TireTypeService
{
    public function create(TireTypeDto $dto): TireType
    {
        return $this->editTireType($dto, new TireType());
    }

    public function update(TireTypeDto $dto, TireType $tireType): TireType
    {
        return $this->editTireType($dto, $tireType);
    }

    private function editTireType(TireTypeDto $dto, TireType $tireType): TireType
    {
        $tireType->active = $dto->isActive();
        $tireType->save();

        foreach (languages() as $language) {
            $translation = $dto->getTranslations()[$language->slug]
                ?? $dto->getTranslations()[defaultLanguage()->slug];

            $tireType->translates()
                ->updateOrCreate(
                    [
                        'language' => $language->slug,
                    ],
                    [
                        'title' => $translation->getTitle(),
                    ]
                );
        }

        return $tireType->refresh();
    }

    public function delete(TireType $tireType): bool
    {
        $tireType->load('tireSpecifications');

        if ($tireType->tireSpecifications->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $tireType->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return TireType::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return TireType::whereKey($ids)->get();
    }

    /**
     * @param iterable<TireType> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
