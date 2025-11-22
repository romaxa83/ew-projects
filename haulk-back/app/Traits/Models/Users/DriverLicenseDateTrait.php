<?php

namespace App\Traits\Models\Users;

/**
 * @see self::getIssuingDate()
 * @method static getIssuingDate
 *
 * @see self::getExpirationDate()
 * @method static getExpirationDate
 */
trait DriverLicenseDateTrait
{
    public function getIssuingDate(): ?string
    {
        return $this->issuing_date_as_str;
    }

    public function getExpirationDate(): ?string
    {
        return $this->expiration_date_as_str;
    }
}

