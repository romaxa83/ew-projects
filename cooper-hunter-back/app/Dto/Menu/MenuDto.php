<?php

namespace App\Dto\Menu;

use App\Dto\TranslateDto;
use App\Enums\Menu\MenuBlockEnum;
use App\Enums\Menu\MenuPositionEnum;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;

class MenuDto
{
    use TranslateDto;

    private bool $active;

    private int $pageId;

    private MenuPositionEnum $position;

    private MenuBlockEnum $block;

    /** @var array<MenuTranslationDto> */
    private array $translations;

    /**
     * @param array $args
     * @return static
     * @throws InvalidEnumMemberException
     */
    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->active = $args['active'];
        $dto->pageId = $args['page_id'];

        $dto->position = new MenuPositionEnum($args['position']);
        $dto->block = new MenuBlockEnum($args['block']);

        $dto->setTranslations($args);

        return $dto;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getPageId(): ?int
    {
        return $this->pageId;
    }

    public function getPosition(): MenuPositionEnum
    {
        return $this->position;
    }

    public function getBlock(): MenuBlockEnum
    {
        return $this->block;
    }
}
