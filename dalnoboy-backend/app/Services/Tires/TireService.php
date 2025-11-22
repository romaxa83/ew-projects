<?php

namespace App\Services\Tires;

use App\Contracts\Models\HasGuard;
use App\Dto\Tires\TireDto;
use App\Enums\Permissions\GuardsEnum;
use App\Exceptions\SameEntityExistsException;
use App\Models\Dictionaries\TireSpecification;
use App\Models\Tires\Tire;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TireService
{
    public function create(TireDto $dto, HasGuard $user): Tire
    {
        return $this->editTire($dto, new Tire(), $user);
    }

    public function update(TireDto $dto, Tire $tire): Tire
    {
        return $this->editTire($dto, $tire);
    }

    private function editTire(TireDto $dto, Tire $tire, ?HasGuard $user = null): Tire
    {
//        dd($dto);
        $this->checkSerialNumber($dto->getSerialNumber(), $tire);

        $tire->active = $user?->getGuard() === GuardsEnum::USER ? true : $dto->isActive();
        $tire->specification_id = $dto->getSpecificationId();
        $tire->relationship_type_id = $dto->getRelationshipTypeId();
        $tire->serial_number = $dto->getSerialNumber();
        $tire->is_moderated = $user?->getGuard() === GuardsEnum::USER ? false : $dto->isModerated();

        if (!is_null($dto->getOgp()) && $tire->id) {
            $tire->ogp = $dto->getOgp();
        }

        $tire->ogp = $tire->ogp ?? TireSpecification::find($dto->getSpecificationId())->ngp;
        $tire->changes_reason_description = $dto->getChangeReasonDescription();
        $tire->changes_reason_id = $dto->getChangeReasonId();
        $tire->save();

        return $tire->refresh();
    }

    private function checkSerialNumber(string $serialNumber, Tire $tire): void
    {
        $exists = Tire::query()
            ->where('serial_number', $serialNumber)
            ->where('id', '<>', $tire->id)
            ->exists();

        if (!$exists) {
            return;
        }

        throw new SameEntityExistsException();
    }

    public function delete(Tire $tire): bool
    {
        return $tire->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return Tire::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return Tire::whereKey($ids)
            ->get();
    }

    /**
     * @param iterable<Tire> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()
                ->save();
        }
    }
}
