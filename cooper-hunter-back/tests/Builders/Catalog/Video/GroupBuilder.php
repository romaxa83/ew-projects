<?php

namespace Tests\Builders\Catalog\Video;

use App\Models\Catalog\Videos\Group;
use Database\Factories\Catalog\Videos\GroupTranslationFactory;
use Illuminate\Support\Str;
use Tests\Builders\BaseBuilder;

class GroupBuilder
{
    private bool $active = Group::DEFAULT_ACTIVE;
    private int $sort = Group::DEFAULT_SORT;

    private $title;
    private $description;

    private bool $withTranslation = false;
    private bool $withLinks = false;

    // Active
    public function getActive()
    {
        return $this->active;
    }
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
    // Sort
    public function getSort()
    {
        return $this->sort;
    }
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    // Title
    public function getTitle()
    {
        if(null == $this->title){
            $this->setTitle(Str::random(10));
        }

        return $this->title;
    }
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    // Description
    public function getDescription()
    {
        if(null == $this->description){
            $this->setDescription(Str::random(100));
        }

        return $this->description;
    }
    public function setDescription(string $desc): self
    {
        $this->description = $desc;

        return $this;
    }

    public function withTranslation(): self
    {
        $this->withTranslation = true;

        return $this;
    }

    public function withLinks(): self
    {
        $this->withLinks = true;

        return $this;
    }

    public function create()
    {
        $model = $this->save();

        if($this->withTranslation){
            $this->saveEnTranslation($model->id);
            $this->saveEsTranslation($model->id);
        }

        if($this->withLinks){
            $builder = app(LinkBuilder::class);

            $builder->setGroupId($model->id)->create();
            $builder->setGroupId($model->id)->create();
        }

        $this->clear();

        return $model;
    }

    private function save()
    {
        $data = [
            'active' => $this->getActive(),
        ];

        return Group::factory()->new($data)->create();
    }

    private function saveEnTranslation($modelId)
    {
        GroupTranslationFactory::new([
            'row_id' => $modelId,
            'language' => 'en',
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'slug' => Str::slug($this->getTitle())
        ])
            ->create();
    }

    private function saveEsTranslation($modelId)
    {
        GroupTranslationFactory::new([
            'row_id' => $modelId,
            'language' => 'es',
            'title' => $this->getTitle() . ' (ES)',
            'description' => $this->getDescription() . ' (ES)',
            'slug' => Str::slug($this->getTitle()),
        ])
            ->create();
    }

    private function clear()
    {
        $this->active = Group::DEFAULT_ACTIVE;
        $this->sort = Group::DEFAULT_SORT;

        $this->title = null;
        $this->description = null;

        $this->withTranslation = false;
        $this->withLinks = false;
    }
}



