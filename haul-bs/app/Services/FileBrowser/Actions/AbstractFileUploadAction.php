<?php

namespace App\Services\FileBrowser\Actions;

use App\Dto\FileBrowser\FileUploadDto;
use Illuminate\Support\Str;

abstract class AbstractFileUploadAction extends AbstractBasicAction implements FileBrowserAction
{

    protected FileUploadDto $dto;

    public function __construct(FileUploadDto $dto)
    {
        parent::__construct($dto->getFileBrowserPrefix());

        $this->dto = $dto;
    }

    abstract public function handle(): FileBrowserAction;

    abstract public function response();

    /**
     * The name and signature of the console command.
     *
     * @return string
     * @var string
     */
    protected function replaceSpecialCharacters(string $name): string
    {
        $name = $this->joditExtensionFix($name);

        $extension = $this->fileBrowser->getExtension($name);

        $name = Str::slug($name);

        $filename = $this->fileBrowser->getFileName($name);

        if (!empty($extension)) {
            $filename = preg_replace("/$extension$/", '.' . $extension, $filename);
        }

        return $filename;
    }

    /*
     * Method for fix jodit feature|bug with rename some extension
     */
    protected function joditExtensionFix(string $name): string
    {
        $extension = $this->fileBrowser->getExtension($name);

        if (!in_array($extension, config('filebrowser.jodit_broken_extension'))) {
            return $name;
        }

        $pattern = sprintf('/(\.%s)$/', $extension);

        return preg_replace($pattern, '', $name);
    }
}
