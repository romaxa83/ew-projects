<?php

namespace App\Dto\About\Pages;

use App\Dto\BaseDictionaryDto;
use App\Models\About\Page;

class PageDto extends BaseDictionaryDto
{
    private string $slug;

    /**
     * @param array $args
     * @return PageDto
     */
    public static function byArgs(array $args): static
    {
        $dto = new static();

        $dto->setModelId($args);
        $dto->slug = $args['slug'];
        $dto->setActive(data_get($args, 'active', $dto->getDefaultActive()));

        $dto->setTranslations($args);

        return $dto;
    }

    protected function getDefaultActive(): bool
    {
        return Page::DEFAULT_ACTIVE;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
}
