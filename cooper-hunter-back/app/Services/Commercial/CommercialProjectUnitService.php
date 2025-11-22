<?php

namespace App\Services\Commercial;

use App\Dto\Commercial\CommercialProjectUnitDto;
use App\Dto\Commercial\CommercialProjectUnitsDto;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectUnit;
use Illuminate\Support\Facades\File;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;

class CommercialProjectUnitService
{
    public function __construct()
    {}

    public function createOrUpdate(CommercialProject $model, CommercialProjectUnitsDto $dto): void
    {
        foreach ($dto->getDtos() as $unitDto){
            foreach ($unitDto->serialNumbers as $serialNumber){
                /** @var $unit CommercialProjectUnit */
                $unit = $model->units->where('serial_number', $serialNumber)->first();
                if($unit){
//                    $this->update($unit, $unitDto);
                } else {
                    $this->create($model, $unitDto->productId, $serialNumber);
                }
            }
        }
    }

    public function create(CommercialProject $project, $productId, $serialNumber): CommercialProjectUnit
    {
        $model = new CommercialProjectUnit();
        $model->commercial_project_id = $project->id;
        $model->serial_number = $serialNumber;
        $model->product_id = $productId;

        $model->save();

        return $model;
    }

    public function removeBySerialNumber(CommercialProject $model, array $serialNumbers): int
    {
        $count = 0;
        foreach ($serialNumbers ?? [] as $serialNumber) {
            if($unit = $model->units->where('serial_number', $serialNumber)->first()){
                $unit->delete();
                $count++;
            }
        }

        return $count;
    }

    public function generateExcel(CommercialProject $model): string
    {
        $basePath = storage_path('app/public/exports/commercial-project/');

        File::ensureDirectoryExists($basePath);
        $fileName = "units-{$model->id}.xlsx";
        $file = $basePath . $fileName;

        $data = [];
        foreach ($model->units as $unit) {
            /** @var $unit CommercialProjectUnit */
            $data[] = [
                __('messages.file.id') => $unit->id,
                __('messages.file.serial_number') => $unit->serial_number,
                __('messages.file.name') => $unit->product->title,
            ];
        }

        $sheets = new SheetCollection([
            'Units' => $data
        ]);

        (new FastExcel($sheets))->export($file);

        return url("/storage/exports/commercial-project/{$fileName}");
    }
}

