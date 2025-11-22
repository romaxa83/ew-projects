<?php

namespace App\Services\FileBrowser\Actions;

use App\Dto\FileBrowser\UploadedFileDto;
use App\Dto\FileBrowser\UploadedFilesInfoDto;
use App\Http\Resources\FileBrowser\FileUploadResource;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @group FileBrowser
 */
class FileUploadAction extends AbstractFileUploadAction
{
    public const ACTION = 'fileUpload';

    private UploadedFilesInfoDto $uploadFilesInfo;

    public function handle(): FileBrowserAction
    {
        $this->checkFilesExists();

        if ($this->hasErrors()) {
            return $this;
        }

        $this->storeFiles();

        return $this;
    }

    private function checkFilesExists(): void
    {
        $path = $this->getPath();

        foreach ($this->getFiles() as $file) {
            $newFilePath = $this->generateNewFilePath($path, $file->getClientOriginalName());

            $this->checkPathExists($newFilePath);
        }
    }

    /**
     * @return UploadedFile[]
     */
    private function getFiles(): array
    {
        return $this->dto->getFiles();
    }

    public function storeFiles(): void
    {
        $path = $this->getPath();
        $uploadedFiles = [];

        foreach ($this->getFiles() as $file) {
            $newFilePath = $this->generateNewFilePath($path, $file->getClientOriginalName());

            $this->fileBrowser->put(
                $newFilePath,
                file_get_contents($file->getRealPath())
            );

            $uploadedFile = UploadedFileDto::byFilePath($newFilePath);
            $uploadedFiles[$uploadedFile->getName()] = $uploadedFile;
        }

        $this->uploadFilesInfo = UploadedFilesInfoDto::byDirUrlAndFiles(
            $this->fileBrowser->getUrl($path),
            $uploadedFiles
        );
    }

    protected function generateNewFilePath(string $path, string $fileName): string
    {
        return $path
            . DIRECTORY_SEPARATOR
            . $this->replaceSpecialCharacters($fileName);
    }

    public function response()
    {
        if ($this->hasErrors()) {
            return $this->getErrorResponse();
        }

        return FileUploadResource::make($this->uploadFilesInfo);
    }
}
