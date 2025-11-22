<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\RecommendationDto;
use App\Models\Dictionaries\Problem;
use App\Models\Dictionaries\Recommendation;
use App\Models\Dictionaries\Regulation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RecommendationService
{
    public function create(RecommendationDto $dto): Recommendation
    {
        return $this->editVehicleClass($dto, new Recommendation());
    }

    public function update(RecommendationDto $dto, Recommendation $recommendation): Recommendation
    {
        return $this->editVehicleClass($dto, $recommendation);
    }

    private function editVehicleClass(RecommendationDto $dto, Recommendation $recommendation): Recommendation
    {
        $recommendation->active = $dto->isActive();
        $recommendation->save();

        $problems = Problem::find($dto->getProblems());
        $recommendation->problems()->detach();
        $recommendation->problems()->attach($problems);

        $regulations = Regulation::find($dto->getRegulations());
        $recommendation->regulations()->detach();
        $recommendation->regulations()->attach($regulations);

        foreach (languages() as $language) {
            $translation = $dto->getTranslations()[$language->slug]
                ?? $dto->getTranslations()[defaultLanguage()->slug];

            $recommendation->translates()
                ->updateOrCreate(
                    [
                        'language' => $language->slug,
                    ],
                    [
                        'title' => $translation->getTitle(),
                    ]
                );
        }

        return $recommendation->refresh();
    }

    public function delete(Recommendation $recommendation): bool
    {
        //TODO chek relations
        return $recommendation->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return Recommendation::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return Recommendation::whereKey($ids)->get();
    }

    /**
     * @param iterable<Recommendation> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
