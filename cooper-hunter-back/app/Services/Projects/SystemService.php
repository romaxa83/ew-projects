<?php

namespace App\Services\Projects;

use App\Dto\Projects\ProjectSystemDto;
use App\Models\Projects\Pivot\SystemUnitPivot;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use App\Services\Warranty\WarrantyService;
use Core\Exceptions\TranslatedException;
use Illuminate\Support\Facades\DB;

class SystemService
{
    public function __construct(protected WarrantyService $warrantyService)
    {
    }

    public function create(Project $project, ProjectSystemDto $dto): System
    {
        return $this->store(
            new System(
                [
                    'project_id' => $project->id
                ]
            ),
            $dto
        );
    }

    protected function store(System $system, ProjectSystemDto $dto): System
    {
        $this->fill($system, $dto);

        $system->touch();

        $this->syncUnits($dto, $system);

        $this->warrantyService->resolveSystemWarrantyStatus($system);

        return $system;
    }

    protected function fill(System $system, ProjectSystemDto $dto): void
    {
        $system->name = $dto->getName();
        $system->description = $dto->getDescription();
    }

    protected function syncUnits(ProjectSystemDto $dto, System $system): void
    {
        $sync = [];

        foreach ($dto->getUnits() as $unit) {
            $sync[$unit->getSerialNumber()] = [
                'product_id' => $unit->getProductId(),
            ];
        }

        //@todo хз, то надо проверять, что серийники добавлены в полном объеме, то не надо
//        $this->assertUnitsAreCompleted(array_keys($sync));

        $system->unitsBySerial()->sync($sync);
    }

    public function updateUsingDto(ProjectSystemDto $dto): System
    {
        $system = System::query()->findOrFail($dto->getId());

        if ($system->warranty_status->requestSent() && count($dto->getUnits()) !== $system->units()->count()) {
            throw new TranslatedException(__('Unable to remove units under warranty from the system'));
        }

        return $this->update($system, $dto);
    }

    protected function update(System $system, ProjectSystemDto $dto): System
    {
        return $this->store($system, $dto);
    }

    public function delete(System $system): bool
    {
        return $system->delete();
    }

    public function deleteUnits(System $system, array $unitIds): bool
    {
        return (bool)$system->units()->detach($unitIds);
    }

    protected function assertUnitsAreCompleted(array $serialNumbers): void
    {
        $throws = static fn() => throw new TranslatedException(__('The set of entered serial numbers is incorrect.'));

        $serials = SystemUnitPivot::query()
            ->select('system_id', 'serial_number')
            ->whereIn('serial_number', $serialNumbers)
            ->getQuery()
            ->get();

        if ($serials->isEmpty()) {
            return;
        }

        $groupedBySystem = [];

        foreach ($serials as $serial) {
            $groupedBySystem[$serial->system_id][] = $serial->serial_number;
        }

        $systemIds = array_keys($groupedBySystem);

        $originalSystemSerialsCount = SystemUnitPivot::query()
            ->whereIn('system_id', $systemIds)
            ->groupBy('system_id')
            ->select('system_id', DB::raw('count(serial_number) as serials_count'))
            ->getQuery()
            ->get()
            ->keyBy('system_id');

        $usedSerialsBySystems = 0;

        foreach ($groupedBySystem as $systemId => $serials) {
            if ($originalSystemSerialsCount->get($systemId)->serials_count !== count($serials)) {
                $throws();
            }

            $usedSerialsBySystems += count($serials);
        }

        $count = $usedSerialsBySystems / count($groupedBySystem);

        if (0 === $count || $count === count($serialNumbers)) {
            return;
        }

        $throws();
    }
}
