<?php

namespace App\Dto\Orders\Dealer;

class OrderFileDto
{
    private string $file;
    private string $name;
    private string $extension;

    public static function make(array $args): self
    {
        $dto = new self();

        $dto->file = $args['file'];
        $dto->name = $args['name'];
        $dto->extension = $args['extension'];

        return $dto;
    }

    public function getDecodedFileData(): string
    {
        return base64_decode($this->getFile());
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }
}