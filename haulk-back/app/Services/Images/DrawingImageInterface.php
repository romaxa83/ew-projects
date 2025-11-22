<?php

namespace App\Services\Images;

use App\Dto\Images\TextLine;
use Illuminate\Http\UploadedFile;

interface DrawingImageInterface
{
    public function addTextOnImage(TextLine $line, UploadedFile $image): UploadedFile;
    public function addDamageLabels(TextLine $line, UploadedFile $image): UploadedFile;
}
