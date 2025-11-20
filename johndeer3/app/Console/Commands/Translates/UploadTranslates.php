<?php

namespace App\Console\Commands\Translates;

use App\Models\Languages;
use App\Models\Report\Feature\Feature;
use App\Models\Report\Feature\FeatureTranslation;
use App\Models\Translate;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class UploadTranslates extends Command
{
    protected $signature = 'jd:upload-translates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uploads languages adn new translates';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
//        $this->createLanguage();
//        $this->uploadTranslates();
//        $this->exportFileTranslation();

        // загрузка переводов из файла _demo_translates_2
//        $this->uploadTranslatesTWO();
        // загрузка не достающих переводов
//        $this->uploadSomeByAlias();
        // загрузка характеристик
        $this->uploadFeatures();
    }

    private function uploadTranslates()
    {
        if(Translate::query()->where('lang','lv')->count() > 200){
            $this->warn('Переводы из файла уже загружены');
            return ;
        }

        $this->info('Импортируем переводы');
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $pathToFile = __DIR__ . '/_demo_translates.xlsx';
        /** Create a new Xls Reader  **/
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        /** Load $inputFileName to a Spreadsheet Object  **/
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($pathToFile);

        // бренды
        $sheetData = $spreadsheet
            ->getSheet(0)
            ->toArray(null, false, true, false);

        foreach ($sheetData as $key => $trans){
            $data = [];
            if($key != 0){

                $data[0]['alias'] = $trans[0];
                $data[0]['text'] = $trans[4];
                $data[0]['lang'] = 'bg';
                $data[0]['model'] = 'site';
                $data[0]['created_at'] = Carbon::now();
                $data[0]['updated_at'] = Carbon::now();

                $data[1]['alias'] = $trans[0];
                $data[1]['text'] = $trans[5];
                $data[1]['lang'] = 'cz';
                $data[1]['model'] = 'site';
                $data[1]['created_at'] = Carbon::now();
                $data[1]['updated_at'] = Carbon::now();

                $data[2]['alias'] = $trans[0];
                $data[2]['text'] = $trans[6];
                $data[2]['lang'] = 'de';
                $data[2]['model'] = 'site';
                $data[2]['created_at'] = Carbon::now();
                $data[2]['updated_at'] = Carbon::now();

                $data[3]['alias'] = $trans[0];
                $data[3]['text'] = $trans[7];
                $data[3]['lang'] = 'da';
                $data[3]['model'] = 'site';
                $data[3]['created_at'] = Carbon::now();
                $data[3]['updated_at'] = Carbon::now();

                $data[4]['alias'] = $trans[0];
                $data[4]['text'] = $trans[8];
                $data[4]['lang'] = 'et';
                $data[4]['model'] = 'site';
                $data[4]['created_at'] = Carbon::now();
                $data[4]['updated_at'] = Carbon::now();

                $data[5]['alias'] = $trans[0];
                $data[5]['text'] = $trans[9];
                $data[5]['lang'] = 'es';
                $data[5]['model'] = 'site';
                $data[5]['created_at'] = Carbon::now();
                $data[5]['updated_at'] = Carbon::now();

                $data[6]['alias'] = $trans[0];
                $data[6]['text'] = $trans[10];
                $data[6]['lang'] = 'fi';
                $data[6]['model'] = 'site';
                $data[6]['created_at'] = Carbon::now();
                $data[6]['updated_at'] = Carbon::now();

                $data[7]['alias'] = $trans[0];
                $data[7]['text'] = $trans[11];
                $data[7]['lang'] = 'fr';
                $data[7]['model'] = 'site';
                $data[7]['created_at'] = Carbon::now();
                $data[7]['updated_at'] = Carbon::now();

                $data[8]['alias'] = $trans[0];
                $data[8]['text'] = $trans[12];
                $data[8]['lang'] = 'el';
                $data[8]['model'] = 'site';
                $data[8]['created_at'] = Carbon::now();
                $data[8]['updated_at'] = Carbon::now();

                $data[9]['alias'] = $trans[0];
                $data[9]['text'] = $trans[13];
                $data[9]['lang'] = 'hr';
                $data[9]['model'] = 'site';
                $data[9]['created_at'] = Carbon::now();
                $data[9]['updated_at'] = Carbon::now();

                $data[10]['alias'] = $trans[0];
                $data[10]['text'] = $trans[14];
                $data[10]['lang'] = 'hu';
                $data[10]['model'] = 'site';
                $data[10]['created_at'] = Carbon::now();
                $data[10]['updated_at'] = Carbon::now();

                $data[11]['alias'] = $trans[0];
                $data[11]['text'] = $trans[15];
                $data[11]['lang'] = 'it';
                $data[11]['model'] = 'site';
                $data[11]['created_at'] = Carbon::now();
                $data[11]['updated_at'] = Carbon::now();

                $data[12]['alias'] = $trans[0];
                $data[12]['text'] = $trans[16];
                $data[12]['lang'] = 'lt';
                $data[12]['model'] = 'site';
                $data[12]['created_at'] = Carbon::now();
                $data[12]['updated_at'] = Carbon::now();

                $data[13]['alias'] = $trans[0];
                $data[13]['text'] = $trans[17];
                $data[13]['lang'] = 'lv';
                $data[13]['model'] = 'site';
                $data[13]['created_at'] = Carbon::now();
                $data[13]['updated_at'] = Carbon::now();

                $data[14]['alias'] = $trans[0];
                $data[14]['text'] = $trans[18];
                $data[14]['lang'] = 'nl';
                $data[14]['model'] = 'site';
                $data[14]['created_at'] = Carbon::now();
                $data[14]['updated_at'] = Carbon::now();

                $data[15]['alias'] = $trans[0];
                $data[15]['text'] = $trans[19];
                $data[15]['lang'] = 'nn';
                $data[15]['model'] = 'site';
                $data[15]['created_at'] = Carbon::now();
                $data[15]['updated_at'] = Carbon::now();

                $data[16]['alias'] = $trans[0];
                $data[16]['text'] = $trans[20];
                $data[16]['lang'] = 'pl';
                $data[16]['model'] = 'site';
                $data[16]['created_at'] = Carbon::now();
                $data[16]['updated_at'] = Carbon::now();

                $data[17]['alias'] = $trans[0];
                $data[17]['text'] = $trans[21];
                $data[17]['lang'] = 'ro';
                $data[17]['model'] = 'site';
                $data[17]['created_at'] = Carbon::now();
                $data[17]['updated_at'] = Carbon::now();

                $data[18]['alias'] = $trans[0];
                $data[18]['text'] = $trans[22];
                $data[18]['lang'] = 'sr';
                $data[18]['model'] = 'site';
                $data[18]['created_at'] = Carbon::now();
                $data[18]['updated_at'] = Carbon::now();

                $data[19]['alias'] = $trans[0];
                $data[19]['text'] = $trans[23];
                $data[19]['lang'] = 'sv';
                $data[19]['model'] = 'site';
                $data[19]['created_at'] = Carbon::now();
                $data[19]['updated_at'] = Carbon::now();

                $data[20]['alias'] = $trans[0];
                $data[20]['text'] = $trans[24];
                $data[20]['lang'] = 'sk';
                $data[20]['model'] = 'site';
                $data[20]['created_at'] = Carbon::now();
                $data[20]['updated_at'] = Carbon::now();

                $data[21]['alias'] = $trans[0];
                $data[21]['text'] = $trans[25];
                $data[21]['lang'] = 'pt';
                $data[21]['model'] = 'site';
                $data[21]['created_at'] = Carbon::now();
                $data[21]['updated_at'] = Carbon::now();

                Translate::insert($data);

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->info(PHP_EOL);

    }

    private function uploadSomeByAlias()
    {
        $this->info('Импортируем переводы');
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $pathToFile = __DIR__ . '/_demo_translates.xlsx';
        /** Create a new Xls Reader  **/
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        /** Load $inputFileName to a Spreadsheet Object  **/
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($pathToFile);

        // бренды
        $sheetData = $spreadsheet
            ->getSheet(0)
            ->toArray(null, false, true, false);

        $aliases = [
//            'send_email',
//            'user_created_successfully',
//            'user_created_successfully',
//            'admin',
//            'Client ID',
//            'Has video',
//            'tractors_title',
//            'images_other',
//            'sprayers_title',
//            'status_acitve',
//            'status_active',
//            'status_inactive',
//            'get_picure_from_camera',
//            'download_link',
//            'Pls, add photo every block!',
//            'video_delivered',

            'model_description_selection',
            'check_your_email',
            'company',
            'fullname',
            'from',
            'to',
            'next',
            'customer_phone',
            'report_is_to_big',
            'features',
        ];

        foreach ($sheetData as $key => $trans){
            $data = [];
            if($key != 0){
                if(in_array($trans[0], $aliases)){

                    $data[0]['alias'] = $trans[0];
                    $data[0]['text'] = $trans[1];
                    $data[0]['lang'] = 'en';
                    $data[0]['model'] = 'site';
                    $data[0]['created_at'] = Carbon::now();
                    $data[0]['updated_at'] = Carbon::now();

                    $data[1]['alias'] = $trans[0];
                    $data[1]['text'] = $trans[2];
                    $data[1]['lang'] = 'ua';
                    $data[1]['model'] = 'site';
                    $data[1]['created_at'] = Carbon::now();
                    $data[1]['updated_at'] = Carbon::now();

                    $data[2]['alias'] = $trans[0];
                    $data[2]['text'] = $trans[3];
                    $data[2]['lang'] = 'ru';
                    $data[2]['model'] = 'site';
                    $data[2]['created_at'] = Carbon::now();
                    $data[2]['updated_at'] = Carbon::now();
                }

                Translate::insert($data);

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->info(PHP_EOL);

    }

    private function uploadTranslatesTWO()
    {
//        if(Translate::query()->where('lang','lv')->count() > 200){
//            $this->warn('Переводы из файла уже загружены');
//            return ;
//        }

        $this->info('Импортируем переводы');
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $pathToFile = __DIR__ . '/_demo_translates_2.xlsx';
        /** Create a new Xls Reader  **/
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        /** Load $inputFileName to a Spreadsheet Object  **/
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($pathToFile);

        $sheetData = $spreadsheet
            ->getSheet(0)
            ->toArray(null, false, true, false);

        $countUpdate = 0;
        $countCreate = 0;

        foreach ($sheetData as $key => $trans){

            $langs = [
               'not', 'en', 'bg', 'cz', 'de', 'da', 'et', 'es', 'fi', 'fr', 'el', 'hr', 'hu', 'it', 'lt', 'lv', 'nl', 'nn', 'pl', 'pt', 'ro', 'sr', 'sv', 'sk'
            ];

            if($key !== 0 && $key !== 0){
                foreach ($langs as $k => $lang){

                    if($k != 0){
                        $t = Translate::query()
                            ->where('lang', $lang)
                            ->where('alias', $trans[0])->first()
                        ;

                        if($t){
                            if(null !== $trans[$k]){
                                $t->text = $trans[$k];
                                $t->save();

                                $countUpdate++;

                                $progressBar->advance();
                            }
                        } else {

                            if(null !== $trans[$k]){
                                $t = new Translate();
                                $t->text = $trans[$k] ?? '';
                                $t->alias = $trans[0];
                                $t->lang = $lang;
                                $t->model = 'site';
                                $t->save();

                                $countCreate++;

                                $progressBar->advance();
                            }
                        }
                    }
                }
            }
        }

        $progressBar->finish();
        $this->info(PHP_EOL);
        $this->info("Update - {$countUpdate}");
        $this->info("Create - {$countCreate}");

    }

    private function uploadFeatures()
    {
        $this->info('Импортируем переводы');
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $pathToFile = __DIR__ . '/_demo_features.xlsx';
        /** Create a new Xls Reader  **/
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        /** Load $inputFileName to a Spreadsheet Object  **/
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($pathToFile);

        $sheetData = $spreadsheet
            ->getSheet(0)
            ->toArray(null, false, true, false);

        $countUpdate = 0;

        foreach ($sheetData as $key => $trans){

            $langs = [
                'en', 'ru', 'ua', 'bg', 'cz', 'de', 'da', 'et', 'es', 'fi', 'fr', 'el', 'hr', 'hu', 'it', 'lt', 'lv', 'nl', 'nn', 'pl', 'pt', 'ro', 'sr', 'sv', 'sk'
            ];
            $aliases = [
                'cm', 'type', 't/ha', 'km/h', 'ha/h', 'l/ha', 'minutes (min)', 'Rx or Single rate', 'type, number',
                'AutoTrac usage (or alternatives), signal accuracy',
                'm', 'mm', 'rpm', 'mechanical/hydraulic', 't/h', 'bar', 'kg', 'psc', 'kg/psi/kPa', 'cm / No',
                'kg/ha or l/ha', 'l', 'population/ha', 'ha'
            ];

            if($key !== 0){
                $id = null;
                $idUnit = null;

                foreach ($langs as $k => $lang){

                    if(null == $id){
                        $feature = FeatureTranslation::query()
                            ->where('lang', $lang)
                            ->where('name', $trans[$k])
                            ->first();

                        if($feature){
                            $id = $feature->feature_id;
                        }

                    } else {
                        if($trans[$k]){
                            $feature = FeatureTranslation::query()
                                ->where('lang', $lang)
                                ->where('feature_id', $id)
                                ->first();

                            if($feature){
                                $feature->name = $trans[$k];
                                $feature->save();

                                $progressBar->advance();
                                $countUpdate++;
                            }

                        }
                    }

                    if(in_array($trans[0], $aliases)){



                        if($trans[$k]){
                            if($lang == 'en'){

                                $feature = FeatureTranslation::query()
                                    ->where('lang', $lang)
                                    ->where('unit', $trans[$k])
                                    ->first();

                                if($feature){
                                    $idUnit = $feature->feature_id;

                                }

                            } else {
                                $feature = FeatureTranslation::query()
                                    ->where('lang', $lang)
                                    ->where('feature_id', $idUnit)
                                    ->first();

                                if($feature){
                                    $feature->unit = $trans[$k];
                                    $feature->save();

                                    $progressBar->advance();
                                    $countUpdate++;
                                }
                            }
                        }
                    }
                }
            }
        }

        $progressBar->finish();
        $this->info(PHP_EOL);
        $this->info("Update - {$countUpdate}");

    }

    private function createLanguage()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('languages')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->dataLanguage();

        $this->info('Создаем языки');
        $progressBar = new ProgressBar($this->output, count($data));
        $progressBar->setFormat('verbose');
        $progressBar->start();

        try {
            \DB::transaction(function () use ($data, $progressBar) {
                foreach ($data as $item){
                    $lang = new Languages();
                    $lang->name = $item['name'];
                    $lang->native = $item['native'];
                    $lang->slug = $item['slug'];
                    $lang->locale = $item['locale'];
                    $lang->default = $item['default'];
                    $lang->save();

                    $progressBar->advance();
                }
            });

            $progressBar->finish();
            $this->info(PHP_EOL);
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    private function dataLanguage(): array
    {
        return [
            [
                'name' => 'English',
                'native' => 'English',
                'slug' => 'en',
                'locale' => 'en_EN',
                'default' => true
            ],
            [
                'name' => 'Ukrainian',
                'native' => 'Український',
                'slug' => 'ua',
                'locale' => 'uk_UA',
                'default' => false
            ],
            [
                'name' => 'Russian',
                'native' => 'Русский',
                'slug' => 'ru',
                'locale' => 'ru_RU',
                'default' => false
            ],
            [
                'name' => 'Bulgarian',
                'native' => 'Български',
                'slug' => 'bg',
                'locale' => 'bg_BG',
                'default' => false
            ],
            [
                'name' => 'Czech',
                'native' => 'čeština',
                'slug' => 'cz',
                'locale' => 'cs_CZ',
                'default' => false
            ],
            [
                'name' => 'German',
                'native' => 'Deutsch',
                'slug' => 'de',
                'locale' => 'de_DE',
                'default' => false
            ],
            [
                'name' => 'Danish',
                'native' => 'dansk',
                'slug' => 'da',
                'locale' => 'da_DK',
                'default' => false
            ],
            [
                'name' => 'Estonian',
                'native' => 'eesti',
                'slug' => 'et',
                'locale' => 'et_EE',
                'default' => false
            ],
            [
                'name' => 'Spanish',
                'native' => 'español',
                'slug' => 'es',
                'locale' => 'es_ES',
                'default' => false
            ],
            [
                'name' => 'Finnish',
                'native' => 'suomi',
                'slug' => 'fi',
                'locale' => 'fi_FI',
                'default' => false
            ],
            [
                'name' => 'French',
                'native' => 'français',
                'slug' => 'fr',
                'locale' => 'fr_FR',
                'default' => false
            ],
            [
                'name' => 'Greek',
                'native' => 'Ελληνικά',
                'slug' => 'el',
                'locale' => 'el_GR',
                'default' => false
            ],
            [
                'name' => 'Croatian',
                'native' => 'hrvatski',
                'slug' => 'hr',
                'locale' => 'hr_HR',
                'default' => false
            ],
            [
                'name' => 'Hungarian',
                'native' => 'magyar',
                'slug' => 'hu',
                'locale' => 'hu_HU',
                'default' => false
            ],
            [
                'name' => 'Italian',
                'native' => 'italiano',
                'slug' => 'it',
                'locale' => 'it_IT',
                'default' => false
            ],
            [
                'name' => 'Lithuanian',
                'native' => 'lietuvių',
                'slug' => 'lt',
                'locale' => 'lt_LT',
                'default' => false
            ],
            [
                'name' => 'Latvian',
                'native' => 'latviešu',
                'slug' => 'lv',
                'locale' => 'lv_LV',
                'default' => false
            ],
            [
                'name' => 'Dutch',
                'native' => 'Nederlands',
                'slug' => 'nl',
                'locale' => 'nl_NL',
                'default' => false
            ],
            [
                'name' => 'Norwegian',
                'native' => 'nynorsk',
                'slug' => 'nn',
                'locale' => 'nn_NO',
                'default' => false
            ],
            [
                'name' => 'Polish',
                'native' => 'polski',
                'slug' => 'pl',
                'locale' => 'pl_PL',
                'default' => false
            ],
            [
                'name' => 'Romania',
                'native' => 'română',
                'slug' => 'ro',
                'locale' => 'ro_RO',
                'default' => false
            ],
            [
                'name' => 'Serbian',
                'native' => 'Srpski',
                'slug' => 'sr',
                'locale' => 'sr_RS',
                'default' => false
            ],
            [
                'name' => 'Swedish',
                'native' => 'svenska',
                'slug' => 'sv',
                'locale' => 'sv_SE',
                'default' => false
            ],
            [
                'name' => 'Slovakian',
                'native' => 'slovenčina',
                'slug' => 'sk',
                'locale' => 'sk_SK',
                'default' => false
            ],
            [
                'name' => 'Portuguese',
                'native' => 'português',
                'slug' => 'pt',
                'locale' => 'pt_PT',
                'default' => false
            ],
        ];
    }

    private function exportFileTranslation()
    {

        if(Translate::query()->where('group', Translate::GROUP_EXCEL)->exists()){
            $this->warn('Переводы из файла в бд для excel перенесены');
            return ;
        }

        $data = $this->uploadTranslationForExcel();

        $this->info('Создаем языки');
        $progressBar = new ProgressBar($this->output, count($data));
        $progressBar->setFormat('verbose');
        $progressBar->start();

        try {
            \DB::transaction(function () use ($data, $progressBar) {

                foreach ($data as $lang => $items){
                    $temp = [];
                    $count = 0;
                    foreach ($items as $alias => $text){
                        $temp[$count]['model'] = Translate::TYPE_SITE;
                        $temp[$count]['group'] = Translate::GROUP_EXCEL;
                        $temp[$count]['lang'] = $lang;
                        $temp[$count]['alias'] = $alias;
                        $temp[$count]['text'] = $text;
                        $temp[$count]['created_at'] = Carbon::now();
                        $temp[$count]['updated_at'] = Carbon::now();
                        $count++;
                    }

                    Translate::insert($temp);

                    $progressBar->advance();
                }
            });

            $progressBar->finish();
            $this->info(PHP_EOL);
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    public function uploadTranslationForExcel()
    {
        return [

            'ru' => [
                'file.id' => 'ID',
                'file.title' => 'Название',
                'file.dealer_name' => 'Имя дилера',
                'file.dealer_company' => 'Компания дилера',
                'file.salesman_name' => 'Имя продавца',
                'file.client_name' => 'Имя клиента',
                'file.client_company' => 'Клиент Компания',
                'file.client_phone' => 'Клиент Телефон',
                'file.client_status' => 'Клиент Статус',
                'file.client_machine' => 'Клиент Техника',
                'file.client_quantity_machine' => 'Клиент Кол-во',
                'file.manufacturer' => 'Производитель',
                'file.equipment_group' => 'Группа оборудования',
                'file.model_description' => 'Описание модели',
                'file.machine_serial_number' => 'Серийный номер машины',
                'file.trailer_model' => 'Модель прицепа',
                'file.sub_manufacture' => 'Производитель приц. техники',
                'file.sub_equipment_group' => 'Приц. техника',
                'file.sub_model_description' => 'Модель приц. техники',
                'file.sub_serial_number' => 'Сер. номер приц. техники',
                'file.ps_name' => 'Специалист по продукту Имя',
                'file.ps_login' => 'Специалист по продукту Логин',
                'file.ps_email' => 'Специалист по продукту Email',
                'file.ps_phone' => 'Специалист по продукту Телефон',
                'file.ps_country' => 'Специалист по продукту Страна',
                'file.created' => 'Создан',
                'file.location' => 'Локация',
                'file.assignment' => 'Назначение',
                'file.result' => 'Результат',
                'file.client_comment' => 'Комментарий клиента',
            ],
            'en' => [
                'file.id' => 'ID',
                'file.title' => 'Title',
                'file.dealer_name' => 'Dealer Name',
                'file.dealer_company' => 'Dealer Company',
                'file.salesman_name' => 'Salesman Name',
                'file.client_name' => 'Client Name',
                'file.client_company' => 'Client Company',
                'file.client_phone' => 'Client Phone',
                'file.client_status' => 'Client Status',
                'file.client_machine' => 'Client Machine',
                'file.client_quantity_machine' => 'Client Quantity',
                'file.manufacturer' => 'Manufacturer',
                'file.equipment_group' => 'Equipment Group',
                'file.model_description' => 'Model Description',
                'file.machine_serial_number' => 'Machine Serial Number',
                'file.trailer_model' => 'Trailer model',
                'file.sub_manufacture' => 'Trailer manufacturer',
                'file.sub_equipment_group' => 'Trailer equipment group',
                'file.sub_model_description' => 'Trailer model description',
                'file.sub_serial_number' => 'Trailer serial number',
                'file.ps_name' => "Product specialist Name",
                'file.ps_login' => "Product specialist Login",
                'file.ps_email' => "Product specialist Email",
                'file.ps_phone' => "Product specialist Phone",
                'file.ps_country' => "Product specialist Country",
                'file.created' => "Created",
                'file.location' => "Location",
                'file.assignment' => "Assignment",
                'file.result' => "Result",
                'file.client_comment' => "Comment of client",
            ],
            'ua' => [
                'file.id' => 'ID',
                'file.title' => 'Назва',
                'file.dealer_name' => "Дилер Ім'я",
                'file.dealer_company' => "Дилер Компанія",
                'file.salesman_name' =>  "Ім'я продавця",
                'file.client_name' => "Клієнт Ім'я",
                'file.client_company' => "Клієнт Компанія",
                'file.client_phone' => "Клієнт Телефон",
                'file.client_status' => "Клієнт Статус",
                'file.client_machine' => "Клієнт Техніка",
                'file.client_quantity_machine' => "Клієнт Кол-во",
                'file.manufacturer' => 'Виробник',
                'file.equipment_group' => "Група обладнання",
                'file.model_description' => "Опис моделі",
                'file.machine_serial_number' =>  "Серійний номер машини",
                'file.trailer_model' => "Модель причепа",
                'file.sub_manufacture' => "Виробник прич. техніки",
                'file.sub_equipment_group' => "Прич. техніка",
                'file.sub_model_description' =>  "Модель прич. техніки",
                'file.sub_serial_number' => "Сер. номер прич. техніки",
                'file.ps_name' => "Product specialist Ім'я",
                'file.ps_login' => "Product specialist Логін",
                'file.ps_email' => "Product specialist Email",
                'file.ps_phone' => "Product specialist Телефон",
                'file.ps_country' => "Product specialist Країна",
                'file.created' => "Створено",
                'file.location' => "Локація",
                'file.assignment' => "Призначення",
                'file.result' => "Результат",
                'file.client_comment' => "Коментар клієнт",
            ],
        ];
    }
}
