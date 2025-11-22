<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\TireWidthDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Dictionaries\TireWidth;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TireWidthService
{
    public function create(TireWidthDto $dto): TireWidth
    {
        return $this->editTireWidth($dto, new TireWidth());
    }

    public function update(TireWidthDto $dto, TireWidth $tireWidth): TireWidth
    {
        return $this->editTireWidth($dto, $tireWidth);
    }

    private function editTireWidth(TireWidthDto $dto, TireWidth $tireWidth): TireWidth
    {
        $tireWidth->active = $dto->isActive();
        $tireWidth->value = $dto->getValue();
        $tireWidth->save();

        return $tireWidth->refresh();
    }

    public function delete(TireWidth $tireWidth): bool
    {
        $tireWidth->load('tireSizes');
        if ($tireWidth->tireSizes->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $tireWidth->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return TireWidth::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return TireWidth::whereKey($ids)
            ->get();
    }

    /**
     * @param iterable<TireWidth> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
