<?php

namespace App\Traits\Models\Users;

/**
 * @see self::getMedicalCardIssuingDate()
 * @method static getMedicalCardIssuingDate
 *
 * @see self::getMedicalCardExpirationDate()
 * @method static getMedicalCardExpirationDate
 *
 * @see self::getMvrReportedDate()
 * @method static getMvrReportedDate
 */
trait DriverMedicalDateTrait
{
    public function getMedicalCardIssuingDate(): ?string
    {
        return $this->medical_card_issuing_date_as_str;
    }

    public function getMedicalCardExpirationDate(): ?string
    {
        return $this->medical_card_expiration_date_as_str;
    }

    public function getMvrReportedDate(): ?string
    {
        return $this->mvr_reported_date_as_str;
    }
}
