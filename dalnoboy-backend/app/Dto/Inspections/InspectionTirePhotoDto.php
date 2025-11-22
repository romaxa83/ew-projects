<?php


namespace App\Dto\Inspections;

use App\Enums\Inspections\TirePhotoType;

class InspectionTirePhotoDto
{
    public string $type;
    public string $fileAsBase64;
    public string $fileName;
    public string $fileExt;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->type = $args['type'];
        $dto->fileAsBase64 = $args['file_as_base_64'];
        $dto->fileName = $args['file_name'];
        $dto->fileExt = $args['file_ext'];

        return $dto;
    }

    public function getDecodedFileData(): string
    {
        return base64_decode($this->fileAsBase64);
    }
}

