<?php

namespace Database\Seeders;


use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\ValueObjects\Money;
use App\ValueObjects\Uuid;

class BrandModelSeeder extends BaseSeeder
{

    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('car_brands')->truncate();
        \DB::table('car_models')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->seedBrand();
        $this->seedModel();
    }

    public function seedBrand()
    {
        $models = array_map('str_getcsv', file(__DIR__.'/_brands.csv'));

        try {
            \DB::transaction(function () use ($models) {
                $insert = [];
                foreach ($models as $key => $item){
                    $insert[$key]['uuid'] = $item[0];
                    $insert[$key]['is_main'] = $item[1];
                    $insert[$key]['active'] = $item[2];
                    $insert[$key]['sort'] = $item[3];
                    $insert[$key]['name'] = $item[4];
                    $insert[$key]['color'] = $item[5];
                    $insert[$key]['hourly_payment'] = ("" != $item[6]) && (0 != $item[6]) && (null != $item[6])
                        ? $item[6] * 100 : null;
                    $insert[$key]['discount_hourly_payment'] = ("" != $item[7]) && (0 != $item[7]) && (null != $item[7])
                        ? $item[7] * 100 : null;
                }

                Brand::insert($insert);
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    public function seedModel()
    {
        $models = array_map('str_getcsv', file(__DIR__.'/_models.csv'));

        try {
            \DB::transaction(function () use ($models) {
                $insert = [];
                foreach ($models as $key => $item){

                    $insert[$key]['uuid'] = $item[0];
                    $insert[$key]['active'] = $item[1];
                    $insert[$key]['sort'] = $item[2];
                    $insert[$key]['name'] = $item[3];
                    $insert[$key]['brand_id'] = $item[4];
                    $insert[$key]['for_credit'] = ("" != $item[5]) ? 0 : 1;
                    $insert[$key]['for_calc'] = ("" != $item[6]) ? 0 : 1;
                }

                Model::insert($insert);
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

//    public function run()
//    {
//        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
//        \DB::table('car_brands')->truncate();
//        \DB::table('car_models')->truncate();
//        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
//
//        $pathToFile = __DIR__ . '/_brands_models.xlsx';
//
//        /** Create a new Xls Reader  **/
//        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
//        /** Load $inputFileName to a Spreadsheet Object  **/
//        $reader->setReadDataOnly(true);
//        $spreadsheet = $reader->load($pathToFile);
//
//        // бренды
//        $sheetData = $spreadsheet
//            ->getSheet(0)
//            ->toArray(null, false, true, false);
//
//        $brands = [];
//        foreach ($sheetData as $key => $data){
//            if($key != 0){
//                $isMain = false;
//                if($data[1] == 'RENAULT' || $data[1] == 'VOLVO' || $data[1] == 'MITSUBISHI'){
//                    $isMain = true;
//                }
//
//                $brands[$key]['id'] = $data[0];
//                $brands[$key]['name'] = $data[1];
//                $brands[$key]['uuid'] = Uuid::create();
//                $brands[$key]['sort'] = $key;
//                $brands[$key]['is_main'] = $isMain;
//            }
//        }
//        Brand::insert($brands);
//
//        // модели
//        $sheetData = $spreadsheet
//            ->getSheet(1)
//            ->toArray(null, false, true, false);
//
//        $models = [];
//        foreach ($sheetData as $key => $data){
//            if($key != 0){
//                if($data[1]){
//                    $models[$key]['id'] = $data[0];
//                    $models[$key]['name'] = $data[1];
//                    $models[$key]['sort'] = $key;
//                    $models[$key]['brand_id'] = $data[2];
//                    $models[$key]['uuid'] = Uuid::create();
//                }
//            }
//        }
//        Model::insert($models);
//    }
}
