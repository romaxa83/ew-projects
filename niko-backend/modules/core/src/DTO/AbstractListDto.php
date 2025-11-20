<?php

namespace WezomCms\Core\DTO;

use Illuminate\Database\Eloquent\Collection;

abstract class AbstractListDto
{
    protected $collection;
    protected $data = [];
    protected $excludeFields = [];

    abstract public function toList();

    public function setCollection($collection)
    {
        if($collection instanceof Collection){
            $this->collection = $collection;
        }

        return $this;
    }

    public function setCount($count)
    {
        if(is_numeric($count)){
            $this->data['total'] = $count;
        }

        return $this;
    }

    // проверяет есть ли поля которые нужно убрать
    protected function existsExcludeFields(): bool
    {
        return !empty($this->excludeFields);
    }

    // убирает поля
    protected function excludeFields(array $data)
    {
        foreach ($this->excludeFields as $field){

            unset($data[$field]);
        }

        return $data;
    }

    // устанавливает поля которые нужно убрать
    public function setExcludeFields(array $fields)
    {
        $this->excludeFields = $fields;

        return $this;
    }
}

