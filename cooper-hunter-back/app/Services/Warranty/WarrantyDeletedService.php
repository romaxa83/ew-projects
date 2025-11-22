<?php

namespace App\Services\Warranty;

use App\Enums\Projects\Systems\WarrantyStatus;
use App\Models\Warranty\Deleted\WarrantyAddressDeleted;
use App\Models\Warranty\Deleted\WarrantyRegistrationDeleted;
use App\Models\Warranty\Deleted\WarrantyRegistrationUnitPivotDeleted;
use App\Models\Warranty\WarrantyRegistration;
use App\Models\Warranty\WarrantyRegistrationUnitPivot;

class WarrantyDeletedService
{
    public function copy(WarrantyRegistration $model): WarrantyRegistrationDeleted
    {
        $model->load(['unitsPivot', 'address']);

        $deleted = new WarrantyRegistrationDeleted();
        $deleted->id = $model->id;
        $deleted->warranty_status = WarrantyStatus::DELETE();
        $deleted->type = $model->type;
        $deleted->notice = $model->notice;
        $deleted->member_type = $model->member_type;
        $deleted->member_id = $model->member_id;
        $deleted->system_id = $model->system_id;
        $deleted->commercial_project_id = $model->commercial_project_id;
        $deleted->user_info = $model->user_info;
        $deleted->product_info = $model->product_info;
        $deleted->created_at = $model->created_at;
        $deleted->updated_at = $model->updated_at;
        $deleted->save();

        foreach ($model->unitsPivot as $unitPivot){
            /** @var $unitPivot WarrantyRegistrationUnitPivot */
            $unitPivotDeleted = new WarrantyRegistrationUnitPivotDeleted();
            $unitPivotDeleted->warranty_registration_deleted_id = $unitPivot->warranty_registration_id;
            $unitPivotDeleted->product_id = $unitPivot->product_id;
            $unitPivotDeleted->serial_number = $unitPivot->serial_number;
            $unitPivotDeleted->save();
        }

        $address = new WarrantyAddressDeleted();
        $address->warranty_id = $deleted->id;
        $address->country_id = $model->address->country_id;
        $address->state_id = $model->address->state_id;
        $address->city = $model->address->city;
        $address->street = $model->address->street;
        $address->zip = $model->address->zip;
        $address->save();

        $model->delete();

        return $deleted;
    }
}
