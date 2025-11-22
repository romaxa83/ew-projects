<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\TireMakeDto;
use App\Enums\Permissions\GuardsEnum;
use App\Exceptions\HasRelatedEntitiesException;
use App\Exceptions\SameEntityExistsException;
use App\Models\Dictionaries\TireMake;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TireMakeService
{
    public function create(TireMakeDto $dto, HasGuard $user): TireMake
    {
        return $this->editTireMake($dto, new TireMake(), $user);
    }

    public function update(TireMakeDto $dto, TireMake $tireMake): TireMake
    {
        return $this->editTireMake($dto, $tireMake);
    }

    private function editTireMake(TireMakeDto $dto, TireMake $tireMake, ?HasGuard $user = null): TireMake
    {
        $tireMake->active = $user?->getGuard() === GuardsEnum::USER ? true : $dto->isActive();
        $tireMake->title = $dto->getTitle();
        $tireMake->is_moderated = $user?->getGuard() === GuardsEnum::USER ? false : $dto->isModerated();
        $tireMake->save();

        return $tireMake->refresh();
    }

    public function delete(TireMake $tireMake): bool
    {
        $tireMake->load(['tireModels', 'tireSpecifications']);

        if ($tireMake->tireModels->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        if ($tireMake->tireSpecifications->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $tireMake->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return TireMake::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return TireMake::whereKey($ids)->get();
    }

    /**
     * @param iterable<TireMake> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }

    public function createOrUpdate(TireMakeDto $dto, HasGuard $user): TireMake
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

    private function findByData(TireMakeDto $dto): ?TireMake
    {
        return TireMake::where('title', $dto->getTitle())->first();
    }
}
