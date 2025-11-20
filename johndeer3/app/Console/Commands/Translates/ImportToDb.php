<?php

namespace App\Console\Commands\Translates;

use App\Models\Translate;
use Illuminate\Console\Command;

class ImportToDb extends Command
{
    protected $signature = 'jd:import-translates-to-db';

    protected $description = 'Importing translations from a file into a database';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  добавляем в массив переводы (которые нужно перегнать в файлы, т.е. системные), после чего запускаем команду
     *  формат
     * 'file.key' => 'text',
     *
     * @throws \Exception
     */
    public function handle()
    {
        $this->info('Записываем перевод бд');
        $hasNewTranslates = false;

        foreach ($this->translates() as $key => $text){

            $temp = explode('::', $key);

            $exist = Translate::query()
                ->where('group', current($temp))
                ->where('alias', last($temp))
                ->exists()
            ;

            if($exist){
                $this->warn("Перевод по ключу [{$key}] есть");
            } else {

                $trans = new Translate();
                $trans->model = 'site';
                $trans->text = $text;
                $trans->lang = 'en';
                $trans->alias = last($temp);
                $trans->group = current($temp);

//                dd($trans);

                $trans->save();

                $this->info("Перевод по ключу [{$key}] загружен");

                $hasNewTranslates = true;
            }
        }

        if($hasNewTranslates){
            // копируем для все языков в бд
            $this->call('jd:copy-translates');
            // перегоняем обратно в файлы
//            $this->call('jd:export-translates');
        }
    }

    private function translates()
    {
        return [
            'excel::file.head_data_report' => 'Main data report',
            'excel::file.head_feature_ground_report' => 'Feature ground',
            'excel::file.head_feature_machine_report' => 'Feature machine',
            'excel::file.head_feature_sub_machine_report' => 'Feature sub-machine',
            'excel::file.head_model' => 'MODEL',
            'excel::file.head_sub_model' => 'SUB MODEL',
        ];
    }
}


