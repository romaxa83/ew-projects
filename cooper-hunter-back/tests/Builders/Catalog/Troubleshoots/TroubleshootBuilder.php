<?php

namespace Tests\Builders\Catalog\Troubleshoots;

use App\Models\Catalog\Troubleshoots\Group;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use Illuminate\Support\Str;
use Tests\Builders\BaseBuilder;

class TroubleshootBuilder
{
    private $active = Troubleshoot::DEFAULT_ACTIVE;
    private int $sort = Troubleshoot::DEFAULT_SORT;

    private $name = 'some troubleshoot';
    private $groupId;

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

    // Name
    public function getName()
    {
        if(null == $this->name){
            $this->setName(Str::random(10));
        }

        return $this->name;
    }
    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function create()
    {
        $model = $this->save();

        $this->clear();

        return $model;
    }

    private function save()
    {
        $data = [
            'active' => $this->getActive(),
            'name' => $this->getName(),
            'group_id' => $this->getGroupId(),
        ];

        return Troubleshoot::factory()->new($data)->create();
    }

    private function clear()
    {
        $this->active = Troubleshoot::DEFAULT_ACTIVE;
        $this->sort = Troubleshoot::DEFAULT_SORT;
        $this->groupId = null;

        $this->name = 'some troubleshoots';
    }

    private function getGroupRandom(): Group
    {
        return app(GroupBuilder::class)->create();
    }
}





