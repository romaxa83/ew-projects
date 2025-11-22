<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\ProblemDto;
use App\Models\Dictionaries\Problem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProblemService
{
    public function create(ProblemDto $dto): Problem
    {
        return $this->editProblem($dto, new Problem());
    }

    public function update(ProblemDto $dto, Problem $problem): Problem
    {
        return $this->editProblem($dto, $problem);
    }

    private function editProblem(ProblemDto $dto, Problem $problem): Problem
    {
        $problem->active = $dto->isActive();
        $problem->save();

        foreach (languages() as $language) {
            $translation = $dto->getTranslations()[$language->slug]
                ?? $dto->getTranslations()[defaultLanguage()->slug];

            $problem->translates()
                ->updateOrCreate(
                    [
                        'language' => $language->slug,
                    ],
                    [
                        'title' => $translation->getTitle(),
                    ]
                );
        }

        return $problem->refresh();
    }

    public function delete(Problem $problem): bool
    {
        //TODO chek relations
        return $problem->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return Problem::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return Problem::whereKey($ids)->get();
    }

    /**
     * @param iterable<Problem> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
