<?php

class CarsSeeder extends BaseSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('car_models')->truncate();
        \DB::table('car_brands')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $pathToFile = __DIR__ . '/_brands_models.xlsx';

        /** Create a new Xls Reader  **/
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        /** Load $inputFileName to a Spreadsheet Object  **/
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($pathToFile);

        // бренды
        $sheetData = $spreadsheet
            ->getSheet(0)
            ->toArray(null, false, true, false);

        $brands = [];
        foreach ($sheetData as $key => $data){
            if($key != 0){
                $brands[$key]['niko_id'] = $data[0];
                $brands[$key]['name'] = $data[1];
                $brands[$key]['sort'] = $key;
            }
        }
        \WezomCms\Cars\Models\Brand::insert($brands);

        // модели
        $sheetData = $spreadsheet
            ->getSheet(1)
            ->toArray(null, false, true, false);

        $models = [];
        foreach ($sheetData as $key => $data){
            if($key != 0){
                if($data[1]){
                    $models[$key]['niko_id'] = $data[0];
                    $models[$key]['name'] = $data[1];
                    $models[$key]['car_brand_id'] = $data[2];
                }
            }
        }
        \WezomCms\Cars\Models\Model::insert($models);

    }
}




