<?php

use Grimzy\LaravelMysqlSpatial\Types\Point;
use WezomCms\Dealerships\Models\Schedule;

class DealershipsSeeder extends BaseSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('dealerships')->truncate();
        \DB::table('dealership_translations')->truncate();
        \DB::table('dealership_schedules')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $pathToFile = __DIR__ . '/_dealerships.xlsx';

        /** Create a new Xls Reader  **/
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        /** Load $inputFileName to a Spreadsheet Object  **/
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($pathToFile);

        $sheetDataUK = $spreadsheet
            ->getSheet(0)
            ->toArray(null, false, true, false);

        $sheetDataRU = $spreadsheet
            ->getSheet(1)
            ->toArray(null, false, true, false);
//        dd($sheetDataRU);

        try {
            \DB::transaction(function () use ($sheetDataUK, $sheetDataRU){
                $translations = ['ru', 'uk'];
                foreach ($sheetDataUK  ?? [] as $key => $data){
                    if($key >= 3){
//                        if($key == 9){
//                            dd($data);
//                        }

                        $brandName = $data[11] == 'MMC' ? 'Mitsubishi' : $data[11];
                        $city = \WezomCms\Regions\Models\CityTranslation::query()->where('name', 'like', trim($data[10]))->first();

                        $brand = \WezomCms\Cars\Models\Brand::query()->where('name', 'like', $brandName)->first();
                        $location = explode(',', $data[4]);

                        $d = new \WezomCms\Dealerships\Models\Dealership();
                        $d->sort = $key;
                        $d->city_id = $city->city_id;
                        $d->brand_id = $brand->id;
                        $d->email = trim($data[7]);
                        $d->site_link = trim($data[8]);
                        $d->location = new Point(trim($location[0]), trim($location[1]), 4326);
                        $d->save();

                        // uk
                        $t_uk = new \WezomCms\Dealerships\Models\DealershipTranslation();
                        $t_uk->locale = 'uk';
                        $t_uk->dealership_id = $d->id;
                        $t_uk->name = trim(explode('/', $data[2])[0]);
                        $t_uk->text = trim($data[5]);
                        $t_uk->services = trim($data[6]);
                        $t_uk->address = trim($data[3]);
                        $t_uk->save();

                        // ru
                        $t_ru = new \WezomCms\Dealerships\Models\DealershipTranslation();
                        $t_ru->locale = 'ru';
                        $t_ru->dealership_id = $d->id;
                        $t_ru->name = trim(explode('/', $sheetDataRU[$key][2])[0]);
                        $t_ru->text = trim($sheetDataRU[$key][5]);
                        $t_ru->services = trim($sheetDataRU[$key][6]);
                        $t_ru->address = trim($sheetDataRU[$key][3]);
                        $t_ru->save();

                        //schedule
                        $excelDate = explode(PHP_EOL, $data[15]);

                        foreach ($excelDate as $dateString){
                            foreach(explode(': ',$dateString) as $dateArr){
                                $parseDateString = explode(': ',$dateString);
                                foreach ($this->dayInExcel($parseDateString[0]) as $day){
                                    $schedule = explode('-', $parseDateString[1]);
                                    $model = new Schedule();
                                    $model->type = Schedule::TYPE_SALON;
                                    $model->dealership_id = $d->id;
                                    $model->day = $day;
                                    $model->work_start = trim($schedule[0]);
                                    $model->work_end = trim($schedule[1]);
                                    $model->save();
                                }
                            }
                        }
                        $excelDate = explode(PHP_EOL, $data[16]);
                        foreach ($excelDate as $dateString){
                            foreach(explode(': ',$dateString) as $dateArr){
                                $parseDateString = explode(': ',$dateString);
                                foreach ($this->dayInExcel($parseDateString[0]) as $day){
                                    $schedule = explode('-', $parseDateString[1]);
//                                    if(!isset($schedule[1])){
//                                        dd($schedule, $key);
//                                    }
                                    $model = new Schedule();
                                    $model->type = Schedule::TYPE_SERVICE;
                                    $model->dealership_id = $d->id;
                                    $model->day = $day;
                                    $model->work_start = trim($schedule[0]);
                                    $model->work_end = trim($schedule[1]);
                                    $model->save();
                                }
                            }
                        }
                    }
                }
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    public function dayInExcel(string $daysString): array
    {
        $daysString = str_replace(' ', '',$daysString);

        $arrDays = [
            'ПН-ПТ' => [Schedule::MONDAY, Schedule::TUESDAY, Schedule::WEDNESDAY, Schedule::THURSDAY, Schedule::FRIDAY],
            'Пн-Пт' => [Schedule::MONDAY, Schedule::TUESDAY, Schedule::WEDNESDAY, Schedule::THURSDAY, Schedule::FRIDAY],
            'ПН-CБ' => [Schedule::MONDAY, Schedule::TUESDAY, Schedule::WEDNESDAY, Schedule::THURSDAY, Schedule::FRIDAY, Schedule::SATURDAY],
            'ПН-Cб' => [Schedule::MONDAY, Schedule::TUESDAY, Schedule::WEDNESDAY, Schedule::THURSDAY, Schedule::FRIDAY, Schedule::SATURDAY],
            'Пн-Cб' => [Schedule::MONDAY, Schedule::TUESDAY, Schedule::WEDNESDAY, Schedule::THURSDAY, Schedule::FRIDAY, Schedule::SATURDAY],
            'ПН-НД' => [Schedule::MONDAY, Schedule::TUESDAY, Schedule::WEDNESDAY, Schedule::THURSDAY, Schedule::FRIDAY, Schedule::SATURDAY, Schedule::SUNDAY],
            'Пн-Нд' => [Schedule::MONDAY, Schedule::TUESDAY, Schedule::WEDNESDAY, Schedule::THURSDAY, Schedule::FRIDAY, Schedule::SATURDAY, Schedule::SUNDAY],
            'СБ-НД' => [Schedule::SATURDAY, Schedule::SUNDAY],
            'СБ' => [Schedule::SATURDAY],
            'Cб' => [Schedule::SATURDAY],
            'НД' => [Schedule::SUNDAY],
            'Нд' => [Schedule::SUNDAY],
        ];

        return array_key_exists($daysString, $arrDays) ? $arrDays[$daysString] : [];
    }
}

