<?php

namespace WezomCms\Dealerships\DTO;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use WezomCms\Core\DTO\AbstractListDto;

class DealershipListDto extends AbstractListDto
{
    protected DealershipDto $dealershipDto;
    protected $point;

    public function __construct()
    {
        $this->dealershipDto = resolve(DealershipDto::class);
    }

    public function setPoint(Point $point)
    {
        $this->point = $point;
    }

    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toList()
    {
        $this->data['center'] = [];
        if(!$this->collection && $this->collection->isEmpty()){
            return $this->data;
        }

        foreach ($this->collection as $key => $model){

            $item = $this->dealershipDto->setModel($model);

            if($this->point instanceof Point){
                $item->setCoordsForDistance($this->point);
            }

            $item = $item->toArray();
            if($this->existsExcludeFields()) {
                $item = $this->excludeFields($item);
            }

            $this->data['center'][$key] = $item;
        }

        return $this->data;
    }
}
