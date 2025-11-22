<?php

namespace Tests\Builders\Catalog\Video;

use App\Models\Catalog\Videos\Group;
use App\Models\Catalog\Videos\VideoLink;
use Database\Factories\Catalog\Videos\VideoLinkTranslationFactory;
use Illuminate\Support\Str;
use Tests\Builders\BaseBuilder;

class LinkBuilder
{
    private $active = VideoLink::DEFAULT_ACTIVE;
    private int $sort = VideoLink::DEFAULT_SORT;

    private $type = null;
    private $title;
    private $description;

    private $link = 'https://youtube.com';
    private $groupId;

    private bool $withTranslation = false;

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

    // Link
    public function getLink()
    {
        if(null == $this->link){
            $this->setLink(Str::random(10));
        }

        return $this->link;
    }
    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    // GroupID
    public function getGroupId()
    {
        if(null === $this->groupId){
            return $this->getGroupRandom()->id;
        }

        return $this->groupId;
    }
    public function setGroupId(int $id): self
    {
        $this->groupId = $id;

        return $this;
    }

    public function withTranslation(): self
    {
        $this->withTranslation = true;

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

    public function setType(string $title): self
    {
        $this->type = $title;

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

    public function create()
    {
        $model = $this->save();

        if($this->withTranslation){
            $this->saveEnTranslation($model->id);
            $this->saveEsTranslation($model->id);
        }

        $this->clear();

        return $model;
    }
    private function saveEnTranslation($modelId)
    {
        VideoLinkTranslationFactory::new([
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
        VideoLinkTranslationFactory::new([
            'row_id' => $modelId,
            'language' => 'es',
            'title' => $this->getTitle() . ' (ES)',
            'description' => $this->getDescription() . ' (ES)',
            'slug' => Str::slug($this->getTitle()),
        ])
            ->create();
    }


    private function save()
    {
        $data = [
            'active' => $this->getActive(),
            'link' => $this->getLink(),
            'group_id' => $this->getGroupId(),
        ];

        if($this->type){
            $data['link_type'] = $this->type;
        }

        return VideoLink::factory()->new($data)->create();
    }

    private function clear()
    {
        $this->active = VideoLink::DEFAULT_ACTIVE;
        $this->sort = VideoLink::DEFAULT_SORT;
        $this->groupId = null;

        $this->link = 'https://youtube.com';

        $this->title = null;
        $this->description = null;

        $this->withTranslation = false;
    }

    private function getGroupRandom(): Group
    {
        return app(GroupBuilder::class)->create();
    }
}




