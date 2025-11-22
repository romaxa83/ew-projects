<?php


namespace App\Dto\Inspections;


use App\Models\Inspections\Inspection;
use Illuminate\Http\UploadedFile;

class InspectionPhotosDto
{
    private ?UploadedFile $stateNumber;
    private ?UploadedFile $vehicle;
    private ?UploadedFile $dataSheet1;
    private ?UploadedFile $dataSheet2;
    private ?UploadedFile $odo;
    private ?UploadedFile $sign;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->stateNumber = $args[Inspection::MC_STATE_NUMBER] ?? null;
        $dto->vehicle = $args[Inspection::MC_VEHICLE] ?? null;
        $dto->dataSheet1 = $args[Inspection::MC_DATA_SHEET_1] ?? null;
        $dto->dataSheet2 = $args[Inspection::MC_DATA_SHEET_2] ?? null;
        $dto->odo = $args[Inspection::MC_ODO] ?? null;
        $dto->sign = $args[Inspection::MC_SIGN] ?? null;

        return $dto;
    }

    /**
     * @return UploadedFile|null
     */
    public function getStateNumber(): ?UploadedFile
    {
        return $this->stateNumber;
    }

    /**
     * @return UploadedFile|null
     */
    public function getVehicle(): ?UploadedFile
    {
        return $this->vehicle;
    }

    /**
     * @return UploadedFile|null
     */
    public function getDataSheet1(): ?UploadedFile
    {
        return $this->dataSheet1;
    }

    /**
     * @return UploadedFile|null
     */
    public function getDataSheet2(): ?UploadedFile
    {
        return $this->dataSheet2;
    }

    /**
     * @return UploadedFile|null
     */
    public function getOdo(): ?UploadedFile
    {
        return $this->odo;
    }

    /**
     * @return UploadedFile|null
     */
    public function getSign(): ?UploadedFile
    {
        return $this->sign;
    }
}
