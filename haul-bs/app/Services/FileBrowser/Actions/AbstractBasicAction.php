<?php

namespace App\Services\FileBrowser\Actions;

use App\Http\Resources\FileBrowser\FileActionErrorResource;
use App\Services\FileBrowser\FileBrowserStorage;

abstract class AbstractBasicAction
{
    protected array $errors = [];

    protected FileBrowserStorage $fileBrowser;

    public function __construct(?string $fileBrowserPrefix = null)
    {
        $this->fileBrowser = resolve(FileBrowserStorage::class, [
            'fileBrowserPrefix' => $fileBrowserPrefix,
        ]);
    }

    public function hasErrors(): bool
    {
        return (bool)count($this->errors);
    }

    protected function getPath(): string
    {
        return remove_trailing_slashes(
            $this->dto->getPath()
        );
    }

    protected function addError(string $message): self
    {
        $this->errors[] = $message;

        return $this;
    }

    protected function getErrorResponse(): FileActionErrorResource
    {
        return FileActionErrorResource::make($this->getErrors());
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function checkPathExists(string $path): void
    {
        if ($this->fileBrowser->exists($path)) {
            $this->errors[] = $path . ' - ' . __('is already exists!');
        }
    }
}
