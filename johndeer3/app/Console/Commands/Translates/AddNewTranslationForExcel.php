<?php

namespace App\Console\Commands\Translates;

use App\Models\Translate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class AddNewTranslationForExcel extends Command
{
    protected $signature = 'jd:add-translate-to-excel';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
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
                'file.head_data_report' => 'Основные данные по отчету',
            ],
            'en' => [
                'file.head_data_report' => 'Main data report',
            ],
        ];
    }
}


