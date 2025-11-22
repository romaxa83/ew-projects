<?php


namespace App\Dto\Utilities;


class DownloadDto
{
    private string $fileName;
    private string $fileExt;
    private array $fileData;
    private string $hash;
    private ?string $handler;
    private string $language;

    public static function byParam(array $param): self
    {
        $dto = new self();

        $dto->fileName = $param['file_name'];
        $dto->fileExt = $param['file_ext'];
        $dto->fileData = $param['file_data'];
        $dto->language = $param['language'];
        $dto->handler = data_get($param, 'handler');

        $dto->hash = md5(
            json_encode($dto->fileData) .
            $dto->fileName .
            $dto->fileExt
        );

        return $dto;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getFileExt(): string
    {
        return $this->fileExt;
    }

    /**
     * @return array
     */
    public function getFileData(): array
    {
        return $this->fileData;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return string|null
     */
    public function getHandler(): ?string
    {
        return $this->handler;
    }
}
