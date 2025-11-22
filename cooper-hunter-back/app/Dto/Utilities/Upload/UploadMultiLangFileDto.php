<?php

namespace App\Dto\Utilities\Upload;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadMultiLangFileDto
{

    private string $language;

    private string|UploadedFile $file;

    public static function byArgs(array $args): UploadMultiLangFileDto
    {
        $dto = new self();

        $dto->language = $args['language'];

        $dto->file = $args['file'];

        return $dto;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getFile(): string|UploadedFile
    {
        return $this->file;
    }

}
