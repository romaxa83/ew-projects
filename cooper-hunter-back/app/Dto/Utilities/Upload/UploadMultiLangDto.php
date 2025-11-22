<?php

namespace App\Dto\Utilities\Upload;

class UploadMultiLangDto
{

    private int $modelId;

    private string $modelType;

    /**@var UploadMultiLangFileDto[] $files */
    private array $files;

    public static function byArgs(array $args): UploadMultiLangDto
    {
        $dto = new self();

        $dto->modelId = (int)$args['model_id'];
        $dto->modelType = $args['model_type'];

        foreach ($dto['files'] as $file) {
            $dto->files[] = UploadMultiLangFileDto::byArgs($file);
        }

        return $dto;
    }

    public function getModelId(): int
    {
        return $this->modelId;
    }

    public function getModelType(): string
    {
        return $this->modelType;
    }

    /**
     * @return UploadMultiLangFileDto[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }
}
