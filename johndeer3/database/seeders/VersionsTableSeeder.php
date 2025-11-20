<?php

namespace Database\Seeders;

use App\Models\Version;
use Illuminate\Database\Seeder;

class VersionsTableSeeder extends Seeder
{
    public function run()
    {
        foreach ($this->getData() as $item){
            if(!$this->checkAliasExist($item['alias'])){

                $model = new Version();
                $model->alias = $item['alias'];

                $model->save();
            }
        }
    }

    private function checkAliasExist($alias)
    {
        return Version::query()
            ->where('alias', $alias)
            ->exists();
    }

    public function getData()
    {
        return [
            ['alias' => Version::DEALERS],
            ['alias' => Version::CLIENTS],
            ['alias' => Version::EQUIPMENT_GROUP],
            ['alias' => Version::MODEL_DESCRIPTION],
            ['alias' => Version::TERRITORIAL_MANAGERS],
            ['alias' => Version::SALES_MANAGERS],
            ['alias' => Version::REGIONS],
            ['alias' => Version::TRANSLATES],
        ];
    }
}
