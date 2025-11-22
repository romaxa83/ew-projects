<?php

namespace App\Dto\Vehicles;

use App\Models\Vehicles\Vehicle;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;

class TrailerDto
{
    protected array $vehicleData;

    protected ?array $tags;

    protected ?array $attachments;
    protected ?UploadedFile $registrationFile = null;
    protected ?UploadedFile $inspectionFile = null;

    protected ?int $gpsDeviceId = null;

    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->vehicleData = [
            'unit_number' => $data['unit_number'],
            'vin' => $data['vin'],
            'make' => $data['make'],
            'model' => $data['model'],
            'year' => $data['year'],
            'license_plate' => $data['license_plate'] ?? null,
            'temporary_plate' => $data['temporary_plate'] ?? null,
            'notes' => $data['notes'] ?? null,
            'owner_id' => $data['owner_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'driver_attach_at' => $data['driver_attach_at'] ?? null,
            'color' => $data['color'] ?? null,
            'gvwr' => $data['gvwr'] ?? null,
            'registration_number' => $data['registration_number'] ?? null,
            'registration_date' => isset($data['registration_date'])
                ? CarbonImmutable::createFromFormat('m/d/Y', $data['registration_date'])
                    ->format('Y-m-d')
                : null,
            'registration_expiration_date' => isset($data['registration_expiration_date'])
                ? CarbonImmutable::createFromFormat('m/d/Y', $data['registration_expiration_date'])
                    ->format('Y-m-d')
                : null,
            'inspection_date' => isset($data['inspection_date'])
                ? CarbonImmutable::createFromFormat('m/d/Y', $data['inspection_date'])
                    ->format('Y-m-d')
                : null,
            'inspection_expiration_date' => isset($data['inspection_expiration_date'])
                ? CarbonImmutable::createFromFormat('m/d/Y', $data['inspection_expiration_date'])
                    ->format('Y-m-d')
                : null,
            'inspection_number' => $data['inspection_number'] ?? null,

            'registration_date_as_str' => $data['registration_date'] ?? null,
            'registration_expiration_date_as_str' => $data['registration_expiration_date'] ?? null,
            'inspection_date_as_str' => $data['inspection_date'] ?? null,
            'inspection_expiration_date_as_str' => $data['inspection_expiration_date'] ?? null,
        ];

        $dto->tags = $data['tags'] ?? [];

        $dto->attachments = $data[Vehicle::ATTACHMENT_FIELD_NAME] ?? [];
        $dto->registrationFile = $data[Vehicle::REGISTRATION_DOCUMENT_NAME] ?? null;
        $dto->inspectionFile = $data[Vehicle::INSPECTION_DOCUMENT_NAME] ?? null;

        $dto->gpsDeviceId = $data['gps_device_id'] ?? null;

        return $dto;
    }

    public function getVehicleData(): array
    {
        return $this->vehicleData;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getRegistrationFile(): ?UploadedFile
    {
        return $this->registrationFile;
    }

    public function getInspectionFile(): ?UploadedFile
    {
        return $this->inspectionFile;
    }

    public function getGpsDeviceId(): ?int
    {
        return $this->gpsDeviceId;
    }
}
