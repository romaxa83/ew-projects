<?php

namespace Tests\Builders\Catalog;

use App\Enums\Categories\CategoryTypeEnum;
use App\Models\Catalog\Categories\Category;
use Database\Factories\Catalog\Categories\CategoryTranslationFactory;
use Illuminate\Support\Str;
use Tests\Builders\BaseBuilder;

class CategoryBuilder
{
    private $id;
    private bool $active = Category::DEFAULT_ACTIVE;
    private int $sort = Category::DEFAULT_SORT;
    private null|int $parentId = null;
    private $type = null;

    private null|string $title = null;
    private null|string $description = null;

    private bool $withTranslation = false;

    public function withTranslation(): self
    {
        $this->withTranslation = true;

        return $this;
    }

    public function create(): Category
    {
        $model = $this->save();

        if ($this->withTranslation) {
            $this->saveEnTranslation($model->id);
            $this->saveEsTranslation($model->id);
        }

        $this->clear();

        return $model;
    }

    private function save(): Category
    {
        $data = [
            'parent_id' => $this->getParentId(),
            'active' => $this->getActive(),
            'type' => $this->type,
        ];

        if($this->getId()){
            $data['id'] = $this->getId();
        }

        return Category::factory()->create($data);
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(int $id): self
    {
        $this->parentId = $id;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function setType(CategoryTypeEnum $value): self
    {
        $this->type = $value;

        return $this;
    }

    private function saveEnTranslation($modelId): void
    {
        CategoryTranslationFactory::new(
            [
                'row_id' => $modelId,
                'language' => 'en',
                'title' => $this->getTitle(),
                'description' => $this->getDescription(),
                'seo_title' => 'custom seo title en',
                'seo_description' => 'custom seo description en',
                'seo_h1' => 'custom seo h1 en',
            ]
        )
            ->create();
    }

    public function getTitle(): string
    {
        if (null === $this->title) {
            $this->setTitle(Str::random(10));
        }

        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        if (null === $this->description) {
            $this->setDescription(Str::random(100));
        }

        return $this->description;
    }

    public function setDescription(string $desc): self
    {
        $this->description = $desc;

        return $this;
    }

    private function saveEsTranslation($modelId): void
    {
        CategoryTranslationFactory::new(
            [
                'row_id' => $modelId,
                'language' => 'es',
                'title' => $this->getTitle().' (ES)',
                'description' => $this->getDescription().' (ES)',
                'seo_title' => 'custom seo title es',
                'seo_description' => 'custom seo description es',
                'seo_h1' => 'custom seo h1 es',
            ]
        )
            ->create();
    }

    private function clear(): void
    {
        $this->parentId = null;
        $this->active = Category::DEFAULT_ACTIVE;
        $this->sort = Category::DEFAULT_SORT;
        $this->type = null;

        $this->title = null;
        $this->description = null;
        $this->id = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
