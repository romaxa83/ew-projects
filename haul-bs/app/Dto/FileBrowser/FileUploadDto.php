<?php

namespace App\Dto\FileBrowser;

use Illuminate\Http\File;

final class FileUploadDto
{
    /**
     * @var string
     */
    private string $action;

    /**
     * @var string
     */
    private string $source;

    /**
     * @var string|null
     */
    private ?string $path;

    /**
     * @var File[]
     */
    private array $files;

    /**
     * @var string|null
     */
    private ?string $url;

    /**
     * @var string|null
     */
    private ?string $fileBrowserPrefix;

    private function __construct(
//        string $action,
        string $source,
        ?string $path = null,
        array $files = [],
        ?string $url = null,
        ?string $fileBrowserPrefix = null
    ) {
//        $this->action = $action;
        $this->source = $source;
        $this->path = $path;
        $this->files = $files;
        $this->url = $url;
        $this->fileBrowserPrefix = $fileBrowserPrefix;
    }

    public static function byParams(
//        string $action,
        string $source = 'default',
        ?string $path = null,
        ?array $files = [],
        ?string $url = null,
        ?string $companyFolderName = null
    ): FileUploadDto {
        return new self(
            $source,
            $path,
            $files,
            $url,
            $companyFolderName
        );
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

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getFileBrowserPrefix(): ?string
    {
        return $this->fileBrowserPrefix;
    }
}
