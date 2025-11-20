<?php

namespace WezomCms\Cars\DTO;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Core\DTO\AbstractListDto;

class TransmissionListDto extends AbstractListDto
{
    protected TransmissionDto $transmissionDto;

    public function __construct()
    {
        $this->transmissionDto = resolve(TransmissionDto::class);
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
            $this->data[$key] = $this->transmissionDto->setModel($model)->toArray();
        }

        return $this->data;
    }
}
