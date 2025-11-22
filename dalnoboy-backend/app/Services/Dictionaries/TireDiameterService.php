<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\TireDiameterDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Dictionaries\TireDiameter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TireDiameterService
{
    public function create(TireDiameterDto $dto): TireDiameter
    {
        return $this->editTireDiameter($dto, new TireDiameter());
    }

    public function update(TireDiameterDto $dto, TireDiameter $tireDiameter): TireDiameter
    {
        return $this->editTireDiameter($dto, $tireDiameter);
    }

    private function editTireDiameter(TireDiameterDto $dto, TireDiameter $tireDiameter): TireDiameter
    {
        $tireDiameter->active = $dto->isActive();
        $tireDiameter->value = $dto->getValue();
        $tireDiameter->save();

        return $tireDiameter->refresh();
    }

    public function delete(TireDiameter $tireDiameter): bool
    {
        $tireDiameter->load('tireSizes');
        if ($tireDiameter->tireSizes->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $tireDiameter->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return TireDiameter::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return TireDiameter::whereKey($ids)->get();
    }

    /**
     * @param iterable<TireDiameter> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
