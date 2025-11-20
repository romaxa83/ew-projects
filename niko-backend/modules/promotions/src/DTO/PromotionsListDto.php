<?php

namespace WezomCms\Promotions\DTO;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use WezomCms\Core\DTO\AbstractListDto;

class PromotionsListDto extends AbstractListDto
{
    protected PromotionsDto $promotionsDto;

    public function __construct()
    {
        $this->promotionsDto = resolve(PromotionsDto::class);
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

            $item = $this->promotionsDto->setModel($model)->toArray();

            if($this->existsExcludeFields()) {
                $item = $this->excludeFields($item);
            }

            $this->data[$key] = $item;
        }

        return $this->data;
    }
}
