<?php


namespace App\Dto\Inspections;


use Carbon\Carbon;

class InspectionDto
{
    private int $vehicleId;
    private int $driverId;
    private ?int $odo;
    private int $inspectionReasonId;
    private ?string $inspectionReasonDescription;
    private bool $unableToSign;
    private bool $offline;
    private ?InspectionPhotosDto $photos;
    private array $tires;
    private Carbon $time;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->vehicleId = $args['vehicle_id'];
        $dto->driverId = $args['driver_id'];
        $dto->odo = $args['odo'] ?? null;
        $dto->inspectionReasonId = $args['inspection_reason_id'];
        $dto->inspectionReasonDescription = $args['inspection_reason_description'] ?? null;
        $dto->unableToSign = $args['unable_to_sign'];
        $dto->offline = $args['is_offline'];
        $dto->photos = !empty($args['photos']) ? InspectionPhotosDto::byArgs($args['photos']) : null;
        $dto->time = Carbon::createFromTimestamp($args['time']);

        $dto->tires = [];
        foreach ($args['tires'] ?? [] as $tire) {
            $dto->tires[] = InspectionTireDto::byArgs($tire);
        }

        return $dto;
    }

    /**
     * @return int
     */
    public function getVehicleId(): int
    {
        return $this->vehicleId;
    }

    /**
     * @return int
     */
    public function getDriverId(): int
    {
        return $this->driverId;
    }

    /**
     * @return int
     */
    public function getOdo(): ?int
    {
        return $this->odo;
    }

    /**
     * @return int
     */
    public function getInspectionReasonId(): int
    {
        return $this->inspectionReasonId;
    }

    /**
     * @return string|null
     */
    public function getInspectionReasonDescription(): ?string
    {
        return $this->inspectionReasonDescription;
    }

    /**
     * @return bool
     */
    public function isUnableToSign(): bool
    {
        return $this->unableToSign;
    }

    /**
     * @return bool
     */
    public function isNotUnableToSign(): bool
    {
        return !$this->unableToSign;
    }

    /**
     * @return bool
     */
    public function isOffline(): bool
    {
        return $this->offline;
    }

    /**
     * @return bool
     */
    public function isNotOffline(): bool
    {
        return !$this->offline;
    }

    /**
     * @return InspectionPhotosDto|null
     */
    public function getPhotos(): ?InspectionPhotosDto
    {
        return $this->photos;
    }

    /**
     * @return InspectionTireDto[]
     */
    public function getTires(): array
    {
        return $this->tires;
    }

    /**
     * @return Carbon
     */
    public function getTime(): Carbon
    {
        return $this->time;
    }
}
