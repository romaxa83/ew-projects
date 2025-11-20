<?php

namespace App\Console\Commands;

use App\Models\Report\Report;
use App\Models\Version;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Repositories\PageRepository;
use App\Resources\Custom\CustomReportPdfResource;
use Illuminate\Console\Command;

class RequestRate extends Command
{
    protected $signature = 'cmd:speed';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
//        Version::getHash(app(ModelDescriptionRepository::class)->getAllForHash());

        $report = Report::find(700);

//        $repo = app(EquipmentGroupRepository::class);


        $time_start = $this->microtime_float();
        //--------------------------------------
        resolve(CustomReportPdfResource::class)->fill($report);
//        $data = $repo->getForHash();
//dd($data);
//        Version::getHash($data);

        //--------------------------------------
        $time_end = $this->microtime_float();
        $time = $time_end - $time_start;

        $this->line("{$time} sec");

    }

    private function microtime_float()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }
}

