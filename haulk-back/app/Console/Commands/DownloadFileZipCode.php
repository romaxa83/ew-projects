<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;
use ZipArchive;

class DownloadFileZipCode extends Command
{

    private const EXCEPT_FILES = [
        'zipcode/usa-cities-zcd.csv',
        'zipcode/time_zone.csv'
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:download-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка и обновление данных в базе';

    /**
     * Execute the console command.
     *
     * https://www.serviceobjects.com/public/zipcode/ZipCodeFiles.zip
     *
     */
    public function handle()
    {
        $this->info('Download file..');

        $storage = Storage::disk('public');

        $files = array_diff(
            $storage->allFiles('zipcode'),
            self::EXCEPT_FILES
        );

        $storage->delete($files);

        $storage->put(
            'zipcode/zipcode.zip',
            file_get_contents(
                'https://www.serviceobjects.com/public/zipcode/ZipCodeFiles.zip',
                false,
                stream_context_create(
                    [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ],
                    ]
                )
            )
        );

        $this->info('Download done');

        $this->info('Extracting file...');

        $zip = new ZipArchive();
        $zip->open($storage->path('zipcode/zipcode.zip'));
        $zip->extractTo($storage->path('zipcode'));

        $this->info('Extracting done');


        $this->info('Renaming files...');

        $allFiles = $storage->files('zipcode');
        foreach ($allFiles as $file) {
            if(preg_match('/uszip/i', $file))
            {
                $storage->move($file, 'zipcode/usa-cities-so.csv');
            }
            if(preg_match('/canad/i', $file))
            {
                $storage->move($file, 'zipcode/canada-cities.csv');
            }
        }

        $this->call('locations:import-canada');
        //$this->call('locations:import-usa-so');

        $this->info('Done');
    }
}
