<?php

namespace App\Models\Report;

use App\Models\JD\EquipmentGroup;
use App\Models\JD\Manufacturer;
use App\Models\JD\ModelDescription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int|null $manufacturer_id
 * @property int|null $equipment_group_id
 * @property int|null $model_description_id
 * @property string|null $trailed_equipment_type
 * @property string|null $trailer_model
 * @property string $serial_number_header
 * @property string $machine_serial_number
 * @property string|null $sub_machine_serial_number
 * @property int|null $sub_manufacturer_id
 * @property int|null $sub_equipment_group_id
 * @property int|null $sub_model_description_id
 * @property int|null $header_brand_id // бренд жатки (актуален для комбайнов)
 * @property int|null $header_model_id // модель жатки (актуален для комбайнов)
 *
 * @property-read ModelDescription $modelDescription
 * @property-read EquipmentGroup $equipmentGroup
 * @property-read Manufacturer $manufacturer
 * @property-read Collection|Report[] $reports
 * @property-read Manufacturer|null $headerBrand
 * @property-read ModelDescription|null $headerModel
 */

class ReportMachine extends Model
{
    use HasFactory;

    public const INDEPENDENT_MACHINE = '1';
    public const WITH_TRAILER_MACHINE = '0';

    public $timestamps = false;

    protected $table = 'reports_machines';

    //relation
    public function equipmentGroup(): BelongsTo
    {
        return $this->belongsTo(EquipmentGroup::class);
    }

    public function modelDescription(): BelongsTo
    {
        return $this->belongsTo(ModelDescription::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function headerBrand(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'header_brand_id');
    }

    public function headerModel(): BelongsTo
    {
        return $this->belongsTo(ModelDescription::class, 'header_model_id');
    }

    public function subEquipmentGroup(): BelongsTo
    {
        return $this->belongsTo(EquipmentGroup::class, 'sub_equipment_group_id', 'id');
    }

    public function subModelDescription(): BelongsTo
    {
        return $this->belongsTo(ModelDescription::class, 'sub_model_description_id');
    }

    public function subManufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'sub_manufacturer_id');
    }

    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class);
    }
}
