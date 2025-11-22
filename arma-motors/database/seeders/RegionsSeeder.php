<?php

namespace Database\Seeders;

use App\Models\Catalogs\Region\City;
use App\Models\Catalogs\Region\CityTranslation;
use App\Models\Catalogs\Region\Region;
use App\Models\Catalogs\Region\RegionTranslation;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class RegionsSeeder extends BaseSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('cities')->truncate();
        \DB::table('city_translations')->truncate();
        \DB::table('regions')->truncate();
        \DB::table('region_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $xml = simplexml_load_file(__DIR__ . '/_regions.xml');

        $count = 0;
        $regionCount = 0;
        foreach ($xml as $key => $item){

            $region = new Region();
            $region->sort = $regionCount;
            $region->save();
            $regionCount++;

            $regionTran = new RegionTranslation();
            $regionTran->name = (string)$item['name'][0];
            $regionTran->lang = 'ru';
            $regionTran->region_id = $region->id;
            $regionTran->save();

            $regionTranUk = new RegionTranslation();
            $regionTranUk->name = (string)$item['name-uk'][0];
            $regionTranUk->lang = 'uk';
            $regionTranUk->region_id = $region->id;
            $regionTranUk->save();

            foreach ($item->city as $cityData){

//                dd($cityData);
//                new Point(trim($location[0]), trim($location[1]), 4326)
                $city = new City();
                $city->region_id = $region->id;
                $city->location = new Point(trim($cityData['lat']), trim($cityData['lon']), 4326);
//                $city->location = (string)$cityData['lat'];
//                $city->lon = (string)$cityData['lon'];
                $city->sort = $count;
                $city->save();
                $count++;

                $cityTran = new CityTranslation();
                $cityTran->name = (string)$cityData['name'];
                $cityTran->city_id = $city->id;
                $cityTran->lang = 'ru';
                $cityTran->save();

                $cityTranUk = new CityTranslation();
                $cityTranUk->name = (string)$cityData['name-uk'];
                $cityTranUk->city_id = $city->id;
                $cityTranUk->lang = 'uk';
                $cityTranUk->save();
            }
        }
    }
}



