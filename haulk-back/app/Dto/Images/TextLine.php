<?php

namespace App\Dto\Images;

class TextLine
{
    private string $text;

    private int $textSize;

    private string $fontPath;

    private string $backgroundColor;

    private function __construct()
    {
    }

    public static function byParams(string $text, string $bColor = null, int $textSize = null, string $fontPath = null)
    {
        $self = new self();

        $self->text = $text;
        $self->textSize = $textSize ?? config('images.inspection.text.size');
        $self->backgroundColor = $bColor ?? config('images.inspection.text.background_color');
        $self->fontPath = $fontPath ?? config('images.inspection.text.font');

        return $self;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getTextSize(): int
    {
        return $this->textSize;
    }

    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    public function getFontPath(): string
    {
        return $this->fontPath;
    }

}
