<?php

namespace WezomCms\ServicesOrders\DTO;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use WezomCms\Core\DTO\AbstractListDto;
use WezomCms\ServicesOrders\Helpers\Price;

class OrderListDto extends AbstractListDto
{
    protected OrderDto $orderDto;
    protected ?Point $point = null;
    protected $includeTotalCost = false;

    public function __construct()
    {
        $this->orderDto = resolve(OrderDto::class);
    }

    public function setPoint(?Point $point)
    {
        $this->point = $point;

        return $this;
    }

    public function includeTotalCost()
    {
        $this->includeTotalCost = true;

        return $this;
    }

    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toList()
    {
        if($this->includeTotalCost){
            $this->data['totalCost'] = 0;
        }

        $this->data['applications'] = [];
        if(!$this->collection && $this->collection->isEmpty()){
            return $this->data;
        }

        foreach ($this->collection as $key => $model){

            if($this->includeTotalCost){
                $this->data['totalCost'] += Price::fromDB($model->price_order_cost);
            }

            $this->data['applications'][$key] = $this->orderDto
                ->setModel($model)
                ->setCoordsForDistance($this->point)
                ->toArray();
        }

        return $this->data;
    }
}
