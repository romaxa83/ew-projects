<?php

namespace Tests\Builders;

use Illuminate\Support\Carbon;
use WezomCms\Catalog\Models\Collections\Collection;

class CollectionsBuilder
{
    private $creatorId;
    private $bublished;
    private $moderatorId;
    private $startAt;
    private $endAt;

    private $data = [];

    public function setCreatorId($value): self
    {
        $this->creatorId = $value;
        $this->data['creator_id'] = $value;
        return $this;
    }

    public function setPublished(bool $value): self
    {
        $this->data['published'] = $value;
        return $this;
    }

    public function setStartAt(Carbon $value): self
    {
        $this->data['start_at'] = $value;
        return $this;
    }

    public function setEndAt(Carbon $value): self
    {
        $this->data['end_at'] = $value;
        return $this;
    }

    public function create(): Collection
    {
        $model = $this->save();

        return $model;
    }

    private function save(): Collection
    {
        return Collection::factory()->new($this->data)->create();
    }

}


