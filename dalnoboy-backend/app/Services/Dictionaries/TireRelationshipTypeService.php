<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\TireRelationshipTypeDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Dictionaries\TireRelationshipType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TireRelationshipTypeService
{
    public function create(TireRelationshipTypeDto $dto): TireRelationshipType
    {
        return $this->editTireRelationshipType($dto, new TireRelationshipType());
    }

    public function update(TireRelationshipTypeDto $dto, TireRelationshipType $tireRelationshipType): TireRelationshipType
    {
        return $this->editTireRelationshipType($dto, $tireRelationshipType);
    }

    private function editTireRelationshipType(TireRelationshipTypeDto $dto, TireRelationshipType $tireRelationshipType): TireRelationshipType
    {
        $tireRelationshipType->active = $dto->isActive();
        $tireRelationshipType->save();

        foreach (languages() as $language) {
            $translation = $dto->getTranslations()[$language->slug]
                ?? $dto->getTranslations()[defaultLanguage()->slug];

            $tireRelationshipType->translates()
                ->updateOrCreate(
                    [
                        'language' => $language->slug,
                    ],
                    [
                        'title' => $translation->getTitle(),
                    ]
                );
        }

        return $tireRelationshipType->refresh();
    }

    public function delete(TireRelationshipType $tireRelationshipType): bool
    {
        $tireRelationshipType->load('tires');

        if ($tireRelationshipType->tires->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $tireRelationshipType->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return TireRelationshipType::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return TireRelationshipType::whereKey($ids)->get();
    }

    /**
     * @param iterable<TireRelationshipType> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
