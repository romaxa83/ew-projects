<?php

namespace App\DTO\Media;

class ImageDTO
{
    private ?string $type;
    private ?string $modelId;
    private string $modelClass;
    private string $model;
    private array $images = [];
    private $image;
    private array $sizes = [];

    private array $config;

    private function __construct($model)
    {
        $this->config = config('image.models');
        if(!array_key_exists($model, $this->config)){
            throw new \InvalidArgumentException("Не валидная модель ({$model}) при загрузке медиа");
        }

        $this->modelClass = $this->config[$model]['class'];
        $this->sizes = $this->config[$model]['sizes'] ?? [];
        $this->model = $model;
    }

    public static function byArgs(array $args): self
    {
        $self = new self($args['model']);

        $self->type = $args['type'] ?? null;
        $self->modelId = $args['modelId'];
        $self->images = $args['images'] ?? [];
        $self->image = $args['image'][0] ?? null;

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

    public function getImages(): array
    {
        return $this->images;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getSizes(): array
    {
        return $this->sizes;
    }

    public function setModelId($modelId): self
    {
        $this->modelId = $modelId;

        return $this;
    }

    public function setImages(array $images)
    {
        $this->images = $images;

        return $this;
    }

    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }
}

