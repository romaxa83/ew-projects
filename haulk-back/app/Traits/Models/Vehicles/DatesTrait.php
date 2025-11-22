<?php

namespace App\Traits\Models\Vehicles;

/**
 * @see self::getRegistrationDate()
 * @method static getRegistrationDate
 *
 * @see self::getRegistrationExpirationDate()
 * @method static getRegistrationExpirationDate
 *
 * @see self::getInspectionDate()
 * @method static getInspectionDate
 *
 * @see self::getInspectionExpirationDate()
 * @method static getInspectionExpirationDate
 */
trait DatesTrait
{
    public function getRegistrationDate(): ?string
    {
        return $this->registration_date_as_str;
    }

    public function getRegistrationExpirationDate(): ?string
    {
        return $this->registration_expiration_date_as_str;
    }

    public function getInspectionDate(): ?string
    {
        return $this->inspection_date_as_str;
    }

    public function getInspectionExpirationDate(): ?string
    {
        return $this->inspection_expiration_date_as_str;
    }
}

