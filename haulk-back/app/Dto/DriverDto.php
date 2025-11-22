<?php

namespace App\Dto;

use App\Models\Users\DriverInfo;
use App\Models\Users\DriverLicense;
use Illuminate\Http\UploadedFile;

class DriverDto
{
    private array $driverInfo;
    private array $driverLicenseData;
    private array $previousDriverLicenseData;
    private ?UploadedFile $medicalCardDocument;
    private ?UploadedFile $mvrDocument;
    private ?UploadedFile $driverLicenseDocument;
    private ?UploadedFile $previousDriverLicenseDocument;

    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->driverInfo = [
            'driver_rate' => $data['driver_rate'] ?? null,
            'notes' => $data['notes'] ?? null,
            'medical_card_number' => $data['medical_card']['card_number'] ?? null,
            'medical_card_issuing_date' => $data['medical_card']['issuing_date'] ?? null,
            'medical_card_expiration_date' => $data['medical_card']['expiration_date'] ?? null,
            'mvr_reported_date' => $data['mvr']['reported_date'] ?? null,
            'medical_card_issuing_date_as_str' => $data['medical_card']['issuing_date'] ?? null,
            'medical_card_expiration_date_as_str' => $data['medical_card']['expiration_date'] ?? null,
            'mvr_reported_date_as_str' => $data['mvr']['reported_date'] ?? null,
            'has_company' => $data['has_company'] ?? false,
            'company_name' => $data['company_info']['name'] ?? null,
            'company_ein' => $data['company_info']['ein'] ?? null,
            'company_address' => $data['company_info']['address'] ?? null,
            'company_city' => $data['company_info']['city'] ?? null,
            'company_zip' => $data['company_info']['zip'] ?? null,
        ];

        $dto->medicalCardDocument = $data['medical_card'][DriverInfo::ATTACHED_MEDICAL_CARD_FILED_NAME] ?? null;
        $dto->mvrDocument = $data['mvr'][DriverInfo::ATTACHED_MVR_FILED_NAME] ?? null;

        $dto->driverLicenseData = [
            'license_number' => $data['driver_license']['license_number'] ?? null,
            'issuing_state_id' => $data['driver_license']['issuing_state_id'] ?? null,
            'issuing_date' => $data['driver_license']['issuing_date'] ?? null,
            'expiration_date' => $data['driver_license']['expiration_date'] ?? null,
            'issuing_date_as_str' => $data['driver_license']['issuing_date'] ?? null,
            'expiration_date_as_str' => $data['driver_license']['expiration_date'] ?? null,
            'category' => $data['driver_license']['category'] ?? null,
            'category_name' => $data['driver_license']['category_name'] ?? null,
            'type' => DriverLicense::TYPE_CURRENT,
        ];
        $dto->driverLicenseDocument = $data['driver_license'][DriverLicense::ATTACHED_DOCUMENT_FILED_NAME] ?? null;

        $dto->previousDriverLicenseData = [
            'license_number' => $data['previous_driver_license']['license_number'] ?? null,
            'issuing_country' => $data['previous_driver_license']['issuing_country'] ?? null,
            'issuing_state_id' => $data['previous_driver_license']['issuing_state_id'] ?? null,
            'issuing_date' => $data['previous_driver_license']['issuing_date'] ?? null,
            'expiration_date' => $data['previous_driver_license']['expiration_date'] ?? null,
            'issuing_date_as_str' => $data['previous_driver_license']['issuing_date'] ?? null,
            'expiration_date_as_str' => $data['previous_driver_license']['expiration_date'] ?? null,
            'category' => $data['previous_driver_license']['category'] ?? null,
            'category_name' => $data['previous_driver_license']['category_name'] ?? null,
            'type' => DriverLicense::TYPE_PREVIOUS,
        ];
        $dto->previousDriverLicenseDocument = $data['previous_driver_license'][DriverLicense::ATTACHED_DOCUMENT_FILED_NAME] ?? null;

        return $dto;
    }

    public function getDriverInfo(): array
    {
        return $this->driverInfo;
    }

    public function getDriverLicenseData(): array
    {
        return $this->driverLicenseData;
    }

    public function getPreviousDriverLicenseData(): array
    {
        return $this->previousDriverLicenseData;
    }

    public function getMedicalCardDocument(): ?UploadedFile
    {
        return $this->medicalCardDocument;
    }

    public function getMvrDocument(): ?UploadedFile
    {
        return $this->mvrDocument;
    }

    public function getDriverLicenseDocument(): ?UploadedFile
    {
        return $this->driverLicenseDocument;
    }

    public function getPreviousDriverLicenseDocument(): ?UploadedFile
    {
        return $this->previousDriverLicenseDocument;
    }
}
