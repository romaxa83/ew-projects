<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\TireModelDto;
use App\Enums\Permissions\GuardsEnum;
use App\Exceptions\HasRelatedEntitiesException;
use App\Exceptions\SameEntityExistsException;
use App\Models\Dictionaries\TireModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TireModelService
{
    public function create(TireModelDto $dto, HasGuard $user): TireModel
    {
        return $this->editTireModel($dto, new TireModel(), $user);
    }

    public function update(TireModelDto $dto, TireModel $tireModel): TireModel
    {
        return $this->editTireModel($dto, $tireModel);
    }

    private function editTireModel(TireModelDto $dto, TireModel $tireModel, ?HasGuard $user = null): TireModel
    {
        $tireModel->active = $user?->getGuard() === GuardsEnum::USER ? true : $dto->isActive();
        $tireModel->tire_make_id = $dto->getTireMakeId();
        $tireModel->title = $dto->getTitle();
        $tireModel->is_moderated = $user?->getGuard() === GuardsEnum::USER ? false : $dto->isModerated();
        $tireModel->save();

        return $tireModel->refresh();
    }

    public function delete(TireModel $tireModel): bool
    {
        $tireModel->load('tireSpecifications');

        if ($tireModel->tireSpecifications->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $tireModel->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return TireModel::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return TireModel::whereKey($ids)->get();
    }

    /**
     * @param iterable<TireModel> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }

    public function createOrUpdate(TireModelDto $dto, HasGuard $user): TireModel
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

    private function findByData(TireModelDto $dto): ?TireModel
    {
        return TireModel::where('title', $dto->getTitle())
            ->where('tire_make_id', $dto->getTireMakeId())
            ->first();
    }
}
