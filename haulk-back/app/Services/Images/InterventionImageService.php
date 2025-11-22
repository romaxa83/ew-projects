<?php

namespace App\Services\Images;

use App\Dto\Images\TextLine;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Gd\Font;
use Intervention\Image\Gd\Shapes\RectangleShape;
use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic;

class InterventionImageService implements DrawingImageInterface
{
    public function addTextOnImage(TextLine $line, UploadedFile $image): UploadedFile
    {
        $processed = ImageManagerStatic::make($image->path());

        $height = $processed->getHeight();
        $width = $processed->getWidth();

        $fontSize = $this->calculateSize($height, $line->getTextSize());

        $this->addRectangle($processed, $height, $width, $line);

        $processed->text(
            $line->getText(),
            5,
            $height,
            function (Font $font) use ($line, $fontSize) {
                $font
                    ->valign('bottom')
                    ->size($fontSize)
                    ->file($line->getFontPath())//
                ;
            }
        )
            ->save($image->path());

        return $image;
    }

    public function addDamageLabels(TextLine $line, UploadedFile $image): UploadedFile
    {
        $processed = ImageManagerStatic::make($image->path());

        $processed->resizeCanvas(
            300,
            0,
            'right',
            true,
            '#ffffff'
        );

        $height = $processed->getHeight();

        $fontSize = $this->calculateSize($height, $line->getTextSize());

        $processed->text(
            $line->getText(),
            10,
            10,
            function (Font $font) use ($line, $fontSize) {
                $font
                    ->valign('top')
                    ->size($fontSize)
                    ->file($line->getFontPath())//
                ;
            }
        )
            ->save($image->path());

        return $image;
    }

    private function calculateSize(int $imageHeight, int $sizeInPercent): int
    {
        return (int)(($imageHeight / 100) * $sizeInPercent);
    }

    private function addRectangle(Image $image, int $height, int $width, TextLine $line): void
    {
        $rectangleHeight = $this->calculateSize($height, $line->getTextSize()) + $this->calculateSize($height, 1);

        $image->rectangle(
            0,
            $height - $rectangleHeight,
            $width,
            $height,
            function (RectangleShape $draw) use ($line) {
                $draw->background($line->getBackgroundColor());
            }
        );
    }
}
