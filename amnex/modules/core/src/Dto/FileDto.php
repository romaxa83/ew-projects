<?php

declare(strict_types=1);

namespace Wezom\Core\Dto;

use Illuminate\Http\UploadedFile;

class FileDto
{
    private string $name;
    private ?string $note;
    public UploadedFile $file;

    public static function build(array $args): FileDto
    {
        $self = new FileDto();

        $self->name = $args['name'];
        $self->note = $args['note'] ?? null;
        $self->file = $args['file'];

        return $self;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getFile(): UploadedFile
    {
        return $this->file;
    }
}
