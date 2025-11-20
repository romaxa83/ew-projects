<?php

namespace WezomCms\Cars\DTO;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Core\DTO\AbstractListDto;

class EngineTypeListDto extends AbstractListDto
{
    protected EngineTypeDto $engineTypeDto;

    public function __construct()
    {
        $this->engineTypeDto = resolve(EngineTypeDto::class);
    }

    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toList()
    {
        if(!$this->collection && $this->collection->isEmpty()){
            return $this->data;
        }

        foreach ($this->collection as $key => $model){
            $this->data[$key] = $this->engineTypeDto->setModel($model)->toArray();
        }

        return $this->data;
    }
}
