<?php

namespace Tests\Builders\Catalog;

use App\Models\Catalog\Features\Value;
use Illuminate\Support\Str;
use Tests\Builders\BaseBuilder;

class ValueBuilder
{
    private bool $active = Value::DEFAULT_ACTIVE;
    private int $sort = Value::DEFAULT_SORT;

    private $title;

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

    public function create()
    {
        $model = $this->save();

        $this->clear();

        return $model;
    }

    private function save(): Value
    {
        $data = [
            'active' => $this->getActive(),
            'title' => $this->getTitle(),
        ];

        return Value::factory()->new($data)->create();
    }

    private function clear()
    {
        $this->active = Value::DEFAULT_ACTIVE;
        $this->sort = Value::DEFAULT_SORT;

        $this->title = null;
    }
}


