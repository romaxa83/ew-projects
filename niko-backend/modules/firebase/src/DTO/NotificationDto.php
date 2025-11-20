<?php

namespace WezomCms\Firebase\DTO;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use WezomCms\Cars\DTO\BrandDto;
use WezomCms\Cars\DTO\ModelDto;
use WezomCms\Cars\DTO\ModelListDto;
use WezomCms\Cars\Models\Brand;
use WezomCms\Core\DTO\AbstractDto;
use WezomCms\Core\Traits\CoordsTrait;
use WezomCms\Core\UseCase\DateFormatter;
use WezomCms\Dealerships\Models\Dealership;
use WezomCms\Firebase\Models\FcmNotification;
use WezomCms\Regions\DTO\CityDto;

class NotificationDto extends AbstractDto
{
    public function __construct()
    {}

    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toArray()
    {
        if(!$this->model){
            return [];
        }

        $notification = $this->model;
        /** @var $notification FcmNotification */
//        dd($notification->order);
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'applicationType' => $notification->order->group->type ?? null,
            'applicationId' => $notification->service_order_id,
            'title' => $notification->data['title'] ?? null,
            'description' => $notification->data['body'] ?? null,
            'timestamp' => DateFormatter::convertTimestampForFront($notification->created_at->timestamp),

        ];
    }
}
