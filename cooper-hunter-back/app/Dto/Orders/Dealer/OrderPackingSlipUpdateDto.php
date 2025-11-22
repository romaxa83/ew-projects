<?php

namespace App\Dto\Orders\Dealer;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class OrderPackingSlipUpdateDto
{
    public ?string $trackingNumber;
    public ?string $trackingCompany;

    /** @var array<UploadedFile> */
    public array $media = [];

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->trackingNumber = data_get($args, 'packing_slip.tracking_number');
        $dto->trackingCompany = data_get($args, 'packing_slip.tracking_company');

        $dto->media = data_get($args, 'media', []);

        return $dto;
    }
}
