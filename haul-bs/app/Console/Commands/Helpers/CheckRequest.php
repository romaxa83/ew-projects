<?php

namespace App\Console\Commands\Helpers;

use App\Dto\Inventories\BrandDto;
use App\Models\Inventories\Brand;
use App\Services\Inventories\BrandService;
use App\Services\Requests\ECom\Commands\Brand\BrandCreateCommand;
use App\Services\Requests\ECom\Commands\Brand\BrandDeleteCommand;
use Illuminate\Console\Command;

class CheckRequest extends Command
{
    protected $signature = 'helpers:check_request';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            $this->info("Done [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function exec()
    {
        $data = [
            'name' => 'Brand exec-48-update',
            'slug' => 'brand-exec-48',
        ];

        Brand::query()->where('slug', $data['slug'])->delete();


        $service = resolve(BrandService::class);
        $model = $service->create(BrandDto::byArgs($data));


//        /** @var $command BrandCreateCommand */
//        $command = resolve(BrandCreateCommand::class);
//        $res = $command->exec($model);


//        $command = resolve(BrandDeleteCommand::class);
//        $res = $command->exec(['id' => 884]);

    }
}

