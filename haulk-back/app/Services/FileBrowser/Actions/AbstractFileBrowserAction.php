<?php

namespace App\Services\FileBrowser\Actions;

use App\Dto\FileBrowser\FileBrowserDto;

abstract class AbstractFileBrowserAction extends AbstractBasicAction implements FileBrowserAction
{
    protected FileBrowserDto $dto;

    public function __construct(FileBrowserDto $dto)
    {
        parent::__construct($dto->getFileBrowserPrefix());

        $this->dto = $dto;
    }

    abstract public function handle(): FileBrowserAction;

    abstract public function response();

    protected function getFrom(): string
    {
        return remove_trailing_slashes(
            $this->dto->getFrom()
        );
    }

    protected function getName(): string
    {
        return $this->dto->getName();
    }

    protected function getNewName(): string
    {
        return $this->replaceSpecialCharacters(
            $this->dto->getNewName()
        );
    }

    protected function replaceSpecialCharacters(string $path): string
    {
        return str_replace(' ', '_', $path);
    }
}
