<?php


namespace App\Enums\Inspections;


use App\Models\Inspections\Inspection;
use Core\Enums\BaseEnum;

class InspectionModerationFieldEnum extends BaseEnum
{
    public const ODO = 'odo';
    public const PHOTO_SIGN = 'photos_' . Inspection::MC_SIGN;
    public const PHOTO_VEHICLE = 'photos_' . Inspection::MC_VEHICLE;
    public const PHOTO_STATE_NUMBER = 'photos_' . Inspection::MC_STATE_NUMBER;
    public const PHOTO_ODO = 'photos_' . Inspection::MC_ODO;
    public const OGP = 'ogp';
}
