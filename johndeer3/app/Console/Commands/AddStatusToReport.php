<?php

namespace App\Console\Commands;

use App\Models\Report\Report;
use App\Type\ReportStatus;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class AddStatusToReport extends Command
{
    protected $signature = 'jd:st-report';

    protected $date;

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $reports = Report::query()
            ->where('verify', true)->get();

        foreach ($reports as $item){
            /** @var $item Report */
            if($item->status !== ReportStatus::VERIFY){
                $item->status = ReportStatus::VERIFY;
                $item->save();

                $progressBar->advance();
            }

        }

        $progressBar->finish();
        echo PHP_EOL;
    }
}
