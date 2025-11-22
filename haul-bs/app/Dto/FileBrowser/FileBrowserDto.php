<?php

namespace App\Dto\FileBrowser;

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Requests\FileManager\FileBrowserRequest;

final class FileBrowserDto
{
    private $action;

    private $source;

    private $path;

    private $from;

    private $name;

    private $newName;

    private ?string $fileBrowserPrefix;

    private function __construct(
        string $action,
        string $source,
        ?string $path = null,
        ?string $from = null,
        ?string $name = null,
        ?string $newName = null,
        ?string $fileBrowserPrefix = null
    ) {
        $this->action = $action;
        $this->source = $source;
        $this->path = $path;
        $this->from = $from;
        $this->name = $name;
        $this->newName = $newName;
        $this->fileBrowserPrefix = $fileBrowserPrefix;
    }

    public static function byRequest(FileBrowserRequest $request): self
    {
        $fileBrowserPrefix = null;

        if ($user = $request->user(Role::GUARD_USER)) {
            $fileBrowserPrefix = $user->getFileBrowserPrefix();
        }

        return new self(
            $request->action,
            $request->source,
            $request->path,
            $request->from,
            $request->name,
            $request->newname,
            $fileBrowserPrefix
        );
    }

    public static function byParams(
        string $action,
        string $source = 'default',
        ?string $path = null,
        ?string $from = null,
        ?string $name = null,
        ?string $newName = null,
        ?string $fileBrowserPrefix = null
    ): FileBrowserDto {
        return new self(
            $action,
            $source,
            $path,
            $from,
            $name,
            $newName,
            $fileBrowserPrefix
        );
    }

    public function hasName(): bool
    {
        return (bool)$this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNewName(): string
    {
        return $this->newName;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getPath(): string
    {
        return $this->path ?? '';
    }

    public function hasPath(): bool
    {
        return (bool)$this->path;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getFileBrowserPrefix(): ?string
    {
        return $this->fileBrowserPrefix;
    }
}

