<?php

namespace App\Dto\Chat\Messages;

use Illuminate\Http\UploadedFile;

class MessageDto
{
    private ?string $text;

    /** @var array<UploadedFile> */
    private array $files;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->text = $args['text'] ?? null;
        $self->files = $args['files'] ?? null;

        return $self;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @return UploadedFile[]
     */
    public function getFiles(): array
    {
        return $this->files ?? [];
    }
}
