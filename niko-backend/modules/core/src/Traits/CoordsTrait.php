<?php

namespace WezomCms\Core\Traits;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use Location\Coordinate;
use Location\Distance\Vincenty;

trait CoordsTrait
{
    private $point;

    protected function checkFromRequest(array $data): bool
    {
        if(array_key_exists('lat', $data) && array_key_exists('lon', $data)){

            $this->point = $this->createPoint($data['lat'], $data['lon']);

            return true;
        }

        return false;
    }

    public function createPoint($lat, $lon): Point
    {
        return new Point($lat, $lon, 4326);
    }

    public function getPoint()
    {
        return $this->point;
    }


    // @see https://github.com/mjaschen/phpgeo
    public function distance(Point $startPoint,Point $finishPoint)
    {
        $coordinate1 = new Coordinate($startPoint->getLat(), $startPoint->getLng());
        $coordinate2 = new Coordinate($finishPoint->getLat(), $finishPoint->getLng());

        $calculator = new Vincenty();

        $meters = $calculator->getDistance($coordinate1, $coordinate2);
        $kilometers = $meters/1000;

        return compact('kilometers','meters');
    }
}
