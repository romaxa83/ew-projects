<?php

namespace App\Dto\Orders\Dealer;

class OrderFilesDto
{
    /** @var array<OrderFileDto> */
    private array $files;

    public static function make(array $args): self
    {
        $dto = new self();

        foreach ($args as $file) {
            $dto->files[] = OrderFileDto::make($file);
        }

        return $dto;
    }

    /**
     * @return array<OrderFileDto>
     */
    public function getFiles(): array
    {
        return $this->files;
    }
}