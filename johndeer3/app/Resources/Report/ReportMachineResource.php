<?php

namespace App\Resources\Report;

use App\Models\Report\ReportMachine;
use App\Resources\JD\EquipmentGroupByReportResource;
use App\Resources\JD\ManufacturerByReportResource;
use App\Resources\JD\ModelDescriptionResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="ReportMachines Resource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="manufacturer", description="Производитель", type="object",
 *         ref="#/components/schemas/ManufacturerByReportResource"
 *     ),
 *     @OA\Property(property="equipment_group", description="Equipment group", type="object",
 *         ref="#/components/schemas/EquipmentGroupByReportResource"
 *     ),
 *     @OA\Property(property="model_description", description="Model description", type="object",
 *         ref="#/components/schemas/ModelDescriptionResource"
 *     ),
 *     @OA\Property(property="trailed_equipment_type", type="string", example="культиватор",
 *         description="Тип прицепного оборудования"
 *     ),
 *     @OA\Property(property="trailer_model", type="string", example="2633VT",
 *         description="Модель прицепного оборудования"
 *     ),
 *     @OA\Property(property="serial_number_header", type="string", example="2487345HRS",
 *         description="Серийный номер жатки"
 *     ),
 *     @OA\Property(property="machine_serial_number", type="string", example="2487345HRS",
 *         description="Серийный номер машины"
 *     ),
 *     @OA\Property(property="header_brand", description="Производитель жатки", type="object",
 *         ref="#/components/schemas/ManufacturerByReportResource"
 *     ),
 *     @OA\Property(property="header_model", description="Модель жатки", type="object",
 *         ref="#/components/schemas/ModelDescriptionResource"
 *     ),
 *     @OA\Property(property="sub", description="Прицепная техника", type="object",
 *         @OA\Property(property="manufacturer", description="Производитель", type="object",
 *             ref="#/components/schemas/ManufacturerByReportResource"
 *         ),
 *         @OA\Property(property="equipment_group", description="Equipment group", type="object",
 *             ref="#/components/schemas/EquipmentGroupByReportResource"
 *         ),
 *         @OA\Property(property="model_description", description="Model description", type="object",
 *             ref="#/components/schemas/ModelDescriptionResource"
 *         ),
 *         @OA\Property(property="machine_serial_number", type="string", example="ЕЕ87345HRS",
 *             description="Серийный номер машины"
 *         ),
 *     ),
 * )
 */

class ReportMachineResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var ReportMachine $machine */
        $machine = $this;

        return [
            'id' => $machine->id,
            'manufacturer' => ManufacturerByReportResource::make($machine->manufacturer),
            'equipment_group' => EquipmentGroupByReportResource::make($machine->equipmentGroup),
            'model_description' => ModelDescriptionResource::make($machine->modelDescription),
            'trailed_equipment_type' => $machine->trailed_equipment_type,
            'trailer_model' => $machine->trailer_model,
            'header_brand' => ManufacturerByReportResource::make($machine->headerBrand),
            'header_model' => ModelDescriptionResource::make($machine->headerModel),
            'serial_number_header' => $machine->serial_number_header,
            'machine_serial_number' => $machine->machine_serial_number,
            'sub' => [
                'machine_serial_number' => $machine->sub_machine_serial_number,
                'manufacturer' => ManufacturerByReportResource::make($machine->subManufacturer),
                'equipment_group' => EquipmentGroupByReportResource::make($machine->subEquipmentGroup),
                'model_description' => ModelDescriptionResource::make($machine->subModelDescription),
            ]
        ];
    }
}
