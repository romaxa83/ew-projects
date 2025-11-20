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

class UploadFromFile extends Command
{
    protected $signature = 'jd:upload-translates-from-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uploads new translates from file';

    protected $files = [];


    public function __construct()
    {
        parent::__construct();

        $this->files = $this->initFiles();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->uploadTranslates();
    }

    private function initFiles(): array
    {
        return [
            [
                'file' => '_demo_translates_4.xlsx',
                'excludeRows' => [0, 1], // какие строки исключить (заголовок)
                // последовательность языков в файле
                'langsRow' => [
                   'alias', 'en', 'bg', 'cz', 'de', 'da', 'et', 'es', 'fi', 'fr', 'el', 'hr', 'hu', 'it', 'lt', 'lv', 'nl', 'nn', 'pl', 'pt', 'ro', 'sr', 'sv', 'sk'
                ],
                'excludeLangsRow' => [0],
                'aliasCell' => 0
            ]
        ];
    }

    private function uploadTranslates()
    {
        foreach ($this->files as $fileData){
            if(file_exists($this->pathToFile($fileData['file']))){

                $this->info("Импортируем переводы из файла [{$fileData['file']}]");
                $progressBar = new ProgressBar($this->output);
                $progressBar->setFormat('verbose');
                $progressBar->start();

                $updated = 0;
                $created = 0;

                foreach ($this->getDataFromFile($this->pathToFile($fileData['file'])) ?? [] as $key => $item){
                    if(!in_array($key, $fileData['excludeRows'])){
                        $alias = $item[$fileData['aliasCell']];

                        foreach ($fileData['langsRow'] ?? [] as $k => $lang){
                            if(!in_array($k, $fileData['langsRow'])){

                                if($item[$k] !== null){
                                    $t = Translate::query()
                                        ->where('lang', $lang)
                                        ->where('alias', $alias)
                                        ->first();

//                                    if($alias === 'units'){

                                    if($t){
                                            $t->text = $item[$k];
                                            $t->save();

                                            $progressBar->advance();
                                            $updated++;
                                        } else {
                                            $t = new Translate();
                                            $t->text = $item[$k];
                                            $t->alias = $alias;
                                            $t->lang = $lang;
                                            $t->model = 'site';
                                            $t->save();

                                            $progressBar->advance();
                                            $created++;
                                        }
//                                    }
                                }
                            }
                        }
                    }
                }

                $progressBar->finish();
                $this->info(PHP_EOL);
                $this->info("Update - {$updated}");
                $this->info("Create - {$created}");

            } else {
                $this->warn("По такому пути - [{$this->pathToFile($fileData['file'])}], нет файла");
            }
        }

    }

    private function pathToFile(string $file): string
    {
        return __DIR__ . '/' . $file;
    }

    private function getDataFromFile(string $pathToFile): array
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        /** Load $inputFileName to a Spreadsheet Object  **/
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($pathToFile);

        return $spreadsheet->getSheet(0)
            ->toArray(null, false, true, false);
    }

}

