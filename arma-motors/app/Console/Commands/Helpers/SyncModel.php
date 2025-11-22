<?php

namespace App\Console\Commands\Helpers;

use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\User\Car;
use Illuminate\Console\Command;

class SyncModel extends Command
{
    protected $signature = 'am:sync_model';

    protected $description = '';

    public function handle()
    {
        try {
            $this->syncBrand();
            $this->syncModel();
        } catch(\Throwable $e){
            $this->error($e->getMessage());
        }
    }

    private function syncBrand()
    {
        foreach ($this->dataBrand() as $item){
            $brand = Brand::query()->where('name', $item['brandName'])->first();
            if($brand){

                if($brand->uuid !== $item['brandId']){
                    $brand->uuid = $item['brandId'];
                    $brand->sys_type = 'update';
                    $brand->save();
                }

            } else {
                $this->warn("Not found brand - [{$item['brandName']}]");
            }
        }
    }

    private function syncModel()
    {
        $brands = Brand::query()->get()->pluck('id','uuid')->toArray();

        foreach ($this->dataModel() as $item){
            $model = Model::query()
                ->where('name', $item['modelName'])
                ->where('brand_id', $brands[$item['brandId']])
                ->first();
            if($model){

                if($model->uuid !== $item['modelId']){
                    $model->uuid = $item['modelId'];
                    $model->sys_type = 'update';
                    $model->save();
                }

            } else {
                $this->warn("Not found model - [{$item['modelName']}]");

                $model = new Model();
                $model->uuid = $item['modelId'];
                $model->name = $item['modelName'];
                $model->brand_id = $brands[$item['brandId']];
                $model->sys_type = 'new';
                $model->save();
            }
        }
    }

    private function dataBrand(): array
    {
        return [
            [
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
        ];
    }

    private function dataModel(): array
    {
        return [
            [
                "modelId" => "ff25bc79-ae04-11ed-96d0-005056b13d2b",
                "modelName" => "COLT",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "04c05775-ae06-11ed-96d0-005056b13d2b",
                "modelName" => "C30",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "77359932-ae08-11ed-96d0-005056b13d2b",
                "modelName" => "GALANT",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "a1177a22-cfb1-11ed-96d9-005056b13d2b",
                "modelName" => "1XC60",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "a1177a23-cfb1-11ed-96d9-005056b13d2b",
                "modelName" => "C30 Elect.",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "e9722b9b-cfb4-11ed-96d9-005056b13d2b",
                "modelName" => "NATIVA",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "e9722b9c-cfb4-11ed-96d9-005056b13d2b",
                "modelName" => "SPACE STAR",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "e9722b9d-cfb4-11ed-96d9-005056b13d2b",
                "modelName" => "L200 ",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "e9722b9e-cfb4-11ed-96d9-005056b13d2b",
                "modelName" => "PAJERO SPORT",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "e9722b9f-cfb4-11ed-96d9-005056b13d2b",
                "modelName" => "LANCER",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "e9722ba0-cfb4-11ed-96d9-005056b13d2b",
                "modelName" => "PAJERO WAGON",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "e9722ba1-cfb4-11ed-96d9-005056b13d2b",
                "modelName" => "ASX",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "e9722ba2-cfb4-11ed-96d9-005056b13d2b",
                "modelName" => "ECLIPSE CROSS",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "e9722ba3-cfb4-11ed-96d9-005056b13d2b",
                "modelName" => "OUTLANDER",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "4866d820-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "DUSTER",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d821-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "ESPACE",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d822-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "MEGANE",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d823-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "Clio RS",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d824-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "KADJAR",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d825-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "FLUENCE",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d826-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "KANGOO",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d827-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "K Ze",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d828-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "SANDERO",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d829-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "Trafic",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d82a-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "TRAFIC VAN ",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d82b-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "LAGUNA",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d82c-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "Megane RS",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d82d-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "EXPRESS",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d82e-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "SYMBOL",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d82f-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "MEGANE SEDAN",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d830-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "ASX 1.6 M/T 2WD",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "4866d831-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "CLIO",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d832-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "LODGY",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d833-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "KANGOO ZE",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d834-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "KOLEOS",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d835-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "DOKKER",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d836-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "MASTER",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d837-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "LATITUDE",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d838-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "TALISMAN",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d839-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "Captur",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d83a-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "DOKKER VAN",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d83b-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "EXPRESS VAN",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d83c-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "ZOE",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d83d-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "ARKANA",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d83e-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "MODUS",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d83f-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "LOGAN MCV",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d840-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "SCENIC",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "4866d841-cfb5-11ed-96d9-005056b13d2b",
                "modelName" => "Logan",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "34a857f0-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V60 CC",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857f1-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "ХС40",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857f2-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V40",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857f3-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC70",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857f4-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC60",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857f5-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC90",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857f6-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "C70 Conv",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857f7-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V90 CC",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857f8-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V50",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857f9-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC60 (-17)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857fa-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V70 (00-08)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857fb-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "1ХС60",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857fc-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V60",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857fd-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S60",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857fe-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "_V60CC",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a857ff-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S60 Cross Country",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85800-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V90",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85801-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC60 2,4D",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85802-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S60 CC",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85803-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S80",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85804-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC70 (08-)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85805-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC60 D5",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85806-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "1XC90",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85807-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S60 II",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85808-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S40",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85809-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC90 NEW",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8580a-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V70 XC (-00)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8580b-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC 60",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8580c-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S40 (04-)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8580d-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S60CC",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8580e-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V70",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8580f-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V40 Cross Country",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85810-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S80 (-06)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85811-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "C70",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85812-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S60 (11-)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85813-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "240 cедан D24",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85814-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S80 2.5T",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85815-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Polestar 2",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85816-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V70 (08-)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85817-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "VOLVO XC90",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85818-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "_V90CC",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85819-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V90CC (17-)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8581a-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC60 T5",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8581b-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S90",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8581c-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC90 AWD",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8581d-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC90 (-15)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8581e-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC60_V246",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8581f-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S40 2.0",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85820-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "1ХС90 ",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85821-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC40",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85822-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "_V40CC",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85823-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V40 CC",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85824-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V40 (-04)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85825-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S60L",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85826-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "С40",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85827-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "C70 (06-)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85828-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V90_CC",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85829-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V90CC",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8582a-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC70 01-07",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8582b-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S60 (-09)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8582c-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "850 4-дв.",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8582d-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC60 ( 17-)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8582e-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V40 (13-)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a8582f-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Volvo XC 60",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85830-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S80 (07-)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85831-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S80L",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85832-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "S80 2.0T",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85833-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "V60 Cross Country",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "34a85834-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "XC60 T6",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "7880b9e5-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Colt 1,5 М/Т",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9e6-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Lancer X 2.0 CVT",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9e7-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "3  RTE12",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880b9e8-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "L-200  2.4 M/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9e9-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "SCENIC III",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880b9ea-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "DUSTER 79H",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880b9eb-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Scenic I (J64)",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880b9ec-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Kangoo AUT15 085 E4",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880b9ed-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Pajero 3.2 A/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9ee-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "L-200  2.4 A/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9ef-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "ASX 2.0 CVT 4WD",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9f0-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "SANDERO STEPWAY",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880b9f1-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "NEW Duster",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880b9f2-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Pajero Sport 2.4 A/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9f3-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Pajero",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9f4-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Outlender New 2.4 CVT",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9f5-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Renault Clio 1.4sx",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880b9f6-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Espace IV (J81)",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880b9f7-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Grandis 2,4 А/Т",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9f8-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Lancer X 2.0 M/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9f9-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Outlender New 2.0 CVT 2WD",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9fa-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Outlender New 3.0 A/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9fb-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Galant 2,4 А/Т",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9fc-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "MEGANE SDN",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880b9fd-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Pajero Sport 2.5 M/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9fe-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Lancer X",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880b9ff-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Pajero Sport 2.5 A/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba00-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Renault LOGAN (46L)",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba01-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Renault DOKKER",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba02-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Kangoo II (K61)",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba03-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Pajero Sport 2.4 M/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba04-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Kangoo II Express (F61)",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba05-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Outlander 2.4 M/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba06-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Grandis 2,4 M/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba07-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "L-200  2.5 A/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba08-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "DOKKER VP KMAMBI M3 DM ",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba09-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "L-200  2.5 M/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba0a-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Outlender New 2.0 CVT 4WD",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba0b-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Pajero Sport 3,0 А/Т",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba0c-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Pajero 3.8 A/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba0d-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Duster (H79)",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba0e-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Kangoo K XTR1 MB",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba0f-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Sandero II Berline 5P (B5",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba10-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Lancer 1,6 М/Т",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba11-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Outlender XL 2.4 CVT",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba12-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Master III",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba13-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "MEGANE SCENIC",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba14-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Megane III",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba15-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Автомобіль Renault Clio",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba16-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Colt 1,3 РКПП",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba17-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Trafic II  (F83)",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba18-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Outlender XL 3.0 A/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba19-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Eclipse Cross (NS)",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "7880ba1a-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Renault Sandero",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "7880ba1b-cfba-11ed-96d9-005056b13d2b",
                "modelName" => "Mitsubishi Outlander",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "ab0b51fc-d445-11ed-96d9-005056b13d2b",
                "modelName" => "VOLVO",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "ab0b51fd-d445-11ed-96d9-005056b13d2b",
                "modelName" => "V90 (17-)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "ab0b51fe-d445-11ed-96d9-005056b13d2b",
                "modelName" => "V70 (-00)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "ab0b51ff-d445-11ed-96d9-005056b13d2b",
                "modelName" => "S90 (17-)",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "ab0b5200-d445-11ed-96d9-005056b13d2b",
                "modelName" => "460 4-дв.",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "ab0b5201-d445-11ed-96d9-005056b13d2b",
                "modelName" => "V50 1,6",
                "brandId" => "04c05774-ae06-11ed-96d0-005056b13d2b",
                "brandName" => "Volvo"
            ],
            [
                "modelId" => "d3557add-d44a-11ed-96d9-005056b13d2b",
                "modelName" => "Outlander 2.0 M/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "d3557ade-d44a-11ed-96d9-005056b13d2b",
                "modelName" => "Автомобиль Renault SYMBOL",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "d3557adf-d44a-11ed-96d9-005056b13d2b",
                "modelName" => "KANGOO EX 14 96",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "d3557ae0-d44a-11ed-96d9-005056b13d2b",
                "modelName" => "Outlander 2.4 A/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "d3557ae1-d44a-11ed-96d9-005056b13d2b",
                "modelName" => "Lancer 2,0 А/Т",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "d3557ae2-d44a-11ed-96d9-005056b13d2b",
                "modelName" => "ASX 1.8 CVT 2WD",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "d3557ae3-d44a-11ed-96d9-005056b13d2b",
                "modelName" => "Pajero 3.5 A/T",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ],
            [
                "modelId" => "d3557ae4-d44a-11ed-96d9-005056b13d2b",
                "modelName" => "Sandero II Berline 5P (B5",
                "brandId" => "4866d81f-cfb5-11ed-96d9-005056b13d2b",
                "brandName" => "RENAULT"
            ],
            [
                "modelId" => "d3557ae5-d44a-11ed-96d9-005056b13d2b",
                "modelName" => "Lancer 2,0 М/Т",
                "brandId" => "ff25bc78-ae04-11ed-96d0-005056b13d2b",
                "brandName" => "MITSUBISHI"
            ]
        ];
    }

    private function cars()
    {
        $res = Car::query()
            ->whereNotNull('uuid')
            ->update(['uuid' => null]);

        $this->info("[sync] - update cars [{$res}]");
    }
}

