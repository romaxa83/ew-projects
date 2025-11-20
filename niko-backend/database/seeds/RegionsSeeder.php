<?php

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

            $region = new \WezomCms\Regions\Models\Region();
            $region->sort = $regionCount;
            $region->save();
            $regionCount++;

            $regionTran = new \WezomCms\Regions\Models\RegionTranslation();
            $regionTran->name = (string)$item['name'][0];
            $regionTran->locale = 'ru';
            $regionTran->region_id = $region->id;
            $regionTran->save();

            $regionTranUk = new \WezomCms\Regions\Models\RegionTranslation();
            $regionTranUk->name = (string)$item['name-uk'][0];
            $regionTranUk->locale = 'uk';
            $regionTranUk->region_id = $region->id;
            $regionTranUk->save();

            foreach ($item->city as $cityData){

                $city = new \WezomCms\Regions\Models\City();
                $city->region_id = $region->id;
                $city->lat = (string)$cityData['lat'];
                $city->lon = (string)$cityData['lon'];
                $city->sort = $count;
                $city->save();
                $count++;

                $cityTran = new \WezomCms\Regions\Models\CityTranslation();
                $cityTran->name = (string)$cityData['name'];
                $cityTran->city_id = $city->id;
                $cityTran->locale = 'ru';
                $cityTran->save();

                $cityTranUk = new \WezomCms\Regions\Models\CityTranslation();
                $cityTranUk->name = (string)$cityData['name-uk'];
                $cityTranUk->city_id = $city->id;
                $cityTranUk->locale = 'uk';
                $cityTranUk->save();
            }
        }
    }
}



