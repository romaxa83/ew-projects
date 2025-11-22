<?php

namespace Tests\Builders\Catalog;

use App\Models\Catalog\Features\Feature;
use Database\Factories\Catalog\Features\FeatureTranslationFactory;
use Illuminate\Support\Str;
use Tests\Builders\BaseBuilder;

class FeatureBuilder
{
    private bool $active = Feature::DEFAULT_ACTIVE;
    private int $sort = Feature::DEFAULT_SORT;

    private ?string $title = null;
    private ?string $description = null;

    private bool $withTranslation = false;

    public function withTranslation(): self
    {
        $this->withTranslation = true;

        return $this;
    }

    public function create()
    {
        $model = $this->save();

        if ($this->withTranslation) {
            $this->saveEnTranslation($model->id);
            $this->saveEsTranslation($model->id);
        }

        $this->clear();

        return $model;
    }

    private function save()
    {
        $data = [
            'active' => $this->getActive(),
        ];

        return Feature::factory()->new($data)->create();
    }

    public function getActive()
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    private function saveEnTranslation($modelId)
    {
        FeatureTranslationFactory::new([
            'row_id' => $modelId,
            'language' => 'en',
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'slug' => Str::slug($this->getTitle())
        ])
            ->create();
    }

    public function getTitle()
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

    public function getDescription()
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

    private function saveEsTranslation($modelId)
    {
        FeatureTranslationFactory::new([
            'row_id' => $modelId,
            'language' => 'es',
            'title' => $this->getTitle().' (ES)',
            'description' => $this->getDescription().' (ES)',
            'slug' => Str::slug($this->getTitle()),
        ])
            ->create();
    }

    private function clear()
    {
        $this->active = Feature::DEFAULT_ACTIVE;
        $this->sort = Feature::DEFAULT_SORT;

        $this->title = null;
        $this->description = null;
    }
}



