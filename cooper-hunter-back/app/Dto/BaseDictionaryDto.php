<?php


namespace App\Dto;


abstract class BaseDictionaryDto
{
    use TranslateDto;

    private ?string $modelGuid = null;
    private ?int $modelId = null;

    private bool $active;

    /**
     * @param array $args
     * @return BaseDictionaryDto
     */
    public static function byArgs(array $args): static
    {
        $dto = new static();

        $dto->setModelGuid($args);
        $dto->setModelId($args);

        $dto->active = data_get($args, 'active', $dto->getDefaultActive());

        $dto->setTranslations($args);

        return $dto;
    }

    abstract protected function getDefaultActive(): bool;

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getModelId(): ?int
    {
        return $this->modelId;
    }

    protected function setModelId(array $args): void
    {
        $id = data_get($args, 'id');

        if (empty($id)) {
            return;
        }

        $this->modelId = (int)$id;
    }

    public function getModelGuid(): ?string
    {
        return $this->modelGuid;
    }

    protected function setModelGuid(array $args): void
    {
        $guid = data_get($args, 'guid');

        if (empty($guid)) {
            return;
        }

        $this->modelGuid = $guid;
    }
}
