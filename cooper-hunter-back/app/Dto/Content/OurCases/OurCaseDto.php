<?php

namespace App\Dto\Content\OurCases;

class OurCaseDto
{
    private bool $active;
    private int $ourCaseCategoryId;

    private array $productIds;

    /** @var array<OurCaseTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->ourCaseCategoryId = $args['our_case_category_id'];
        $dto->active = $args['active'];
        $dto->productIds = $args['product_ids'] ?? [];

        foreach ($args['translations'] as $translation) {
            $dto->translations[] = OurCaseTranslationDto::byArgs($translation);
        }

        return $dto;
    }

    public function getOurCaseCategoryId(): int
    {
        return $this->ourCaseCategoryId;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getProductIds(): array
    {
        return $this->productIds;
    }

    /**
     * @return OurCaseTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
