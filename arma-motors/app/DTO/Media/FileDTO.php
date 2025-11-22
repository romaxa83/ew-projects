<?php

namespace App\DTO\Media;

class FileDTO
{
    private null|string $type;
    private null|string $modelId;
    private string $modelClass;
    private string $model;
    private array $files = [];
    private $file;

    private function __construct($model)
    {
        $config = config('file.models');
        if(!array_key_exists($model, $config)){
            throw new \InvalidArgumentException("Не валидная модель ({$model}) при загрузке медиа");
        }

        $this->modelClass = $config[$model]['class'];
        $this->model = $model;
    }

    public static function byArgs(array $args): self
    {
        $self = new self($args['model']);

        $self->type = $args['type'] ?? null;
        $self->modelId = $args['modelId'];
        $self->files = $args['files'] ?? [];
        $self->file = $args['file'][0] ?? null;

        return $self;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getModelClass()
    {
        return $this->modelClass;
    }

    public function getModelId()
    {
        return $this->modelId;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setModelId($modelId): self
    {
        $this->modelId = $modelId;

        return $this;
    }

    public function setFiles(array $files)
    {
        $this->files = $files;

        return $this;
    }

    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }
}
