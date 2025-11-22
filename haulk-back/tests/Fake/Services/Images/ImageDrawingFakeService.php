<?php

namespace Tests\Fake\Services\Images;

use App\Dto\Images\TextLine;
use App\Services\Images\DrawingImageInterface;
use Illuminate\Http\UploadedFile;

class ImageDrawingFakeService implements DrawingImageInterface
{
    public TextLine $line;

    public UploadedFile $image;

    public function addTextOnImage(TextLine $line, UploadedFile $image): UploadedFile
    {
        $this->line = $line;

        $this->image = $image;

        return $image;
    }

    public function addDamageLabels(TextLine $line, UploadedFile $image): UploadedFile
    {
        $this->line = $line;

        $this->image = $image;

        return $image;
    }

    public function getLine(): TextLine
    {
        return $this->line;
    }
}
