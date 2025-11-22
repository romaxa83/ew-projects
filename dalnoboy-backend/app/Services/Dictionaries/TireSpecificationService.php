<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\TireSpecificationDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Dictionaries\TireSpecification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TireSpecificationService
{
    public function create(TireSpecificationDto $dto): TireSpecification
    {
        return $this->editTireSpecification($dto, new TireSpecification());
    }

    public function update(TireSpecificationDto $dto, TireSpecification $tireSpecification): TireSpecification
    {
        return $this->editTireSpecification($dto, $tireSpecification);
    }

    private function editTireSpecification(
        TireSpecificationDto $dto,
        TireSpecification $tireSpecification
    ): TireSpecification {
        $tireSpecification = $this->getSameSpecification($dto, $tireSpecification);

        $tireSpecification->active = !isBackOffice() ? true : $dto->isActive();
        $tireSpecification->make_id = $dto->getMakeId();
        $tireSpecification->model_id = $dto->getModelId();
        $tireSpecification->type_id = $dto->getTypeId();
        $tireSpecification->size_id = $dto->getSizeId();
        $tireSpecification->ngp = $dto->getNgp();
        $tireSpecification->is_moderated = !isBackOffice() ? false : $dto->isModerated();
        $tireSpecification->save();

        return $tireSpecification->refresh();
    }

    private function getSameSpecification(
        TireSpecificationDto $dto,
        TireSpecification $specification
    ): TireSpecification {
        $result = TireSpecification::query()
            ->where('make_id', $dto->getMakeId())
            ->where('model_id', $dto->getModelId())
            ->where('size_id', $dto->getSizeId())
            ->where('id', '<>', $specification->id)
            ->first();

        return $result ? $result : $specification;
    }

    public function delete(TireSpecification $tireSpecification): bool
    {
        $tireSpecification->load('tires');

        if ($tireSpecification->tires->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $tireSpecification->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return TireSpecification::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return TireSpecification::whereKey($ids)->get();
    }

    /**
     * @param iterable<TireSpecification> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
