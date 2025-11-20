<?php

namespace WezomCms\Dealerships\DTO;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use WezomCms\Cars\DTO\BrandDto;
use WezomCms\Cars\DTO\ModelDto;
use WezomCms\Cars\DTO\ModelListDto;
use WezomCms\Cars\Models\Brand;
use WezomCms\Core\DTO\AbstractDto;
use WezomCms\Core\Traits\CoordsTrait;
use WezomCms\Dealerships\Models\Dealership;
use WezomCms\Dealerships\Models\Schedule;
use WezomCms\Regions\DTO\CityDto;

class DealershipDto extends AbstractDto
{
    use CoordsTrait;

    private $point;
    private BrandDto $brandDto;
    private CityDto $cityDto;
    private ModelListDto $modelListDto;

    public function __construct()
    {
        $this->brandDto = resolve(BrandDto::class);
        $this->cityDto = resolve(CityDto::class);
        $this->modelListDto = resolve(ModelListDto::class);
    }

    public function setCoordsForDistance(Point $point)
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

        $dealership = $this->model;

        /** @var $dealership Dealership */
        return [
            'id' => $dealership->id,
            'name' => $dealership->name,
            'brand' => $this->brandDto->setModel($dealership->brand)->toArray(),
//            'modelsForTrade' => $this->modelListDto->setCollection($dealership->brand->modelsForTrade)->toList(),
            'email' => $dealership->email,
            'phone' => $dealership->getPhonesWithDesc(),
            'address' => $dealership->address,
            'webLink' => $dealership->site_link,
            'description' => $dealership->text,
            'servicesDescription' => $dealership->services,
            'city' => $this->cityDto->setModel($dealership->city)->toArray(),
            'scheduleSalon' => $dealership->getScheduleForFront(Schedule::TYPE_SALON),
            'scheduleService' => $dealership->getScheduleForFront(Schedule::TYPE_SERVICE),
            'coordinates' => $this->coords($dealership),
            'distance' => $this->getDistance($this->point, $dealership->location),
            'photoLinks' => $this->imagesAsArray($dealership->gallery)
        ];
    }

    public function getDistance($pointStart, $pointFinish)
    {

        if($pointStart && $pointFinish){
            $distance = $this->distance($pointStart, $pointFinish)['kilometers'];
            return round($distance, 3);
        }

        return null;
    }
}

