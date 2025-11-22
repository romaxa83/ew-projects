<?php

namespace App\Dto\Orders;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

class InspectExteriorDto
{
    private int $photoId;

    private float $latitude;

    private float $longitude;

    private UploadedFile $image;

    private Carbon $time;

    private function __construct()
    {
    }

    public static function byParams(
        int $photoId,
        float $latitude,
        float $longitude,
        UploadedFile $image,
        Carbon $time
    ) {
        $self = new self();

        $self->photoId = $photoId;
        $self->latitude = $latitude;
        $self->longitude = $longitude;
        $self->image = $image;
        $self->time = $time;

        return $self;
    }

    public function getPhotoId(): int
    {
        return $this->photoId;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getPhoto(): UploadedFile
    {
        return $this->image;
    }

    public function getTime(): Carbon
    {
        return $this->time;
    }

}
