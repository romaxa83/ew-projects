<?php

namespace WezomCms\ServicesOrders\DTO;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use WezomCms\Core\DTO\AbstractDto;
use WezomCms\Core\UseCase\DateFormatter;
use WezomCms\Dealerships\DTO\DealershipDto;
use WezomCms\Regions\DTO\CityDto;
use WezomCms\Services\DTO\InsuranceDto;
use WezomCms\Services\DTO\StoServiceDto;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\Users\DTO\CarDto;

class OrderDto extends AbstractDto
{
    private DealershipDto $dealershipDto;
    private CityDto $cityDto;
    private StoServiceDto $stoServiceDto;
    private InsuranceDto $insuranceDto;
    private CarDto $carDto;

    private $point;

    public function __construct()
    {
        $this->dealershipDto = \App::make(DealershipDto::class);
        $this->cityDto = \App::make(CityDto::class);
        $this->stoServiceDto = \App::make(StoServiceDto::class);
        $this->insuranceDto = \App::make(InsuranceDto::class);
        $this->carDto = \App::make(CarDto::class);
    }

    public function setCoordsForDistance(?Point $point)
    {
        $this->point = $point;

        return $this;
    }

    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toArray()
    {
        if(!$this->model){
            return [];
        }

        $order = $this->model;

        $dealership = $this->dealershipDto->setModel($order->dealership);
        if($this->point){
           $dealership->setCoordsForDistance($this->point);
        }

        /** @var $order ServicesOrder */
        $data = [
            'id' => $order->id,
            'type' => $order->group->type,
            'isUsersVehicle' => $order->is_users_vehicle,
            'vehicle' => $this->carDto->setModel($order->car)->toArray(),
            'dealerCenter' => $dealership->toArray(),
            'city' => $order->city ? $this->cityDto->setModel($order->city)->toArray() : null,
            'comment' => $order->comment,
            'service' => $order->service ? $this->stoServiceDto->setModel($order->service)->toArray() : null,
            'mileage' => $order->mileage,
            'timestamp' => $order->isClose()
                ? DateFormatter::convertDateForFront($order->closed_at)
                : DateFormatter::convertDateForFront($order->final_date ? $order->final_date : $order->on_date),
            'serviceRating' => $order->rating_services === 0 ? null : $order->rating_services,
            'orderRating' => $order->rating_order === 0 ? null : $order->rating_order,
        ];

        if($order->final_order_cost){
            $data['price'] = $order->final_order_cost;
        }

        if($order->service_discount){
            $data['discount'] = $order->service_discount;
        }

        return $data;
    }
}
