<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\TireSizeDto;
use App\Enums\Permissions\GuardsEnum;
use App\Exceptions\HasRelatedEntitiesException;
use App\Exceptions\SameEntityExistsException;
use App\Models\Dictionaries\TireSize;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TireSizeService
{
    public function create(TireSizeDto $dto, HasGuard $user): TireSize
    {
        return $this->editTireSize($dto, new TireSize(), $user);
    }

    public function update(TireSizeDto $dto, TireSize $tireSize): TireSize
    {
        return $this->editTireSize($dto, $tireSize);
    }

    private function editTireSize(TireSizeDto $dto, TireSize $tireSize, ?HasGuard $user = null): TireSize
    {
        $tireSize->active = $user?->getGuard() === GuardsEnum::USER ? true : $dto->isActive();
        $tireSize->tire_width_id = $dto->getTireWidthId();
        $tireSize->tire_height_id = $dto->getTireHeightId();
        $tireSize->tire_diameter_id = $dto->getTireDiameterId();
        $tireSize->is_moderated = $user?->getGuard() === GuardsEnum::USER ? false : $dto->isModerated();
        $tireSize->save();

        return $tireSize->refresh();
    }

    public function delete(TireSize $tireSize): bool
    {
        $tireSize->load('tireSpecifications');

        if ($tireSize->tireSpecifications->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $tireSize->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return TireSize::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return TireSize::whereKey($ids)->get();
    }

    /**
     * @param iterable<TireSize> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }

    public function createOrUpdate(TireSizeDto $dto, HasGuard $user): TireSize
    {
        $item = $this->findByData($dto);

        if (!$item) {
            return $this->create($dto, $user);
        }

        if (!$dto->isOffline()) {
            throw new SameEntityExistsException();
        }

        return $item;
    }

    private function findByData(TireSizeDto $dto): ?TireSize
    {
        return TireSize::where('tire_width_id', $dto->getTireWidthId())
            ->where('tire_height_id', $dto->getTireHeightId())
            ->where('tire_diameter_id', $dto->getTireDiameterId())
            ->first();
    }
}
