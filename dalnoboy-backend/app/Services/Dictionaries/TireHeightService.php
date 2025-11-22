<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\TireHeightDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Dictionaries\TireHeight;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TireHeightService
{
    public function create(TireHeightDto $dto): TireHeight
    {
        return $this->editTireHeight($dto, new TireHeight());
    }

    public function update(TireHeightDto $dto, TireHeight $tireHeight): TireHeight
    {
        return $this->editTireHeight($dto, $tireHeight);
    }

    private function editTireHeight(TireHeightDto $dto, TireHeight $tireHeight): TireHeight
    {
        $tireHeight->active = $dto->isActive();
        $tireHeight->value = $dto->getValue();
        $tireHeight->save();

        return $tireHeight->refresh();
    }

    public function delete(TireHeight $tireHeight): bool
    {
        $tireHeight->load('tireSizes');
        if ($tireHeight->tireSizes->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $tireHeight->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return TireHeight::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return TireHeight::whereKey($ids)->get();
    }

    /**
     * @param iterable<TireHeight> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
