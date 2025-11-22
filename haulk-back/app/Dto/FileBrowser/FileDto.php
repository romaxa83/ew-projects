<?php

namespace App\Dto\FileBrowser;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class FileDto implements Arrayable
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string|null
     */
    private $thumb;

    /**
     * @var Carbon
     */
    private $changed;

    /**
     * @var string
     */
    private $size;

    public static function byAttributes(array $attributes)
    {
        $self = new self();

        $self->fileName = Arr::get($attributes, 'fileName');
        $self->thumb = Arr::get($attributes, 'thumb');
        $self->changed = Arr::get($attributes, 'changed');
        $self->size = Arr::get($attributes, 'size');

        return $self;
    }

    public function toArray(): array
    {
        return [
            'file' => $this->getFileName(),
            'thumb' => $this->hasThumb() ? $this->getThumb() : null,
            'changed' => $this->getChanged()->format('d/m/Y H:m A'),
            'size' => $this->getSizeInKb(),
            'thumbIsAbsolute' => $this->hasThumb(),
        ];
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function hasThumb(): bool
    {
        return !!$this->thumb;
    }

    public function getThumb(): string
    {
        return $this->thumb;
    }

    public function getChanged(): Carbon
    {
        return $this->changed;
    }

    public function getSizeInKb(): string
    {
        $size = $this->getSize() / 1024;

        $size = round($size, config('filebrowser.file_size_accuracy'));

        return $size . ' kb';
    }

    public function getSize(): string
    {
        return $this->size;
    }
}
