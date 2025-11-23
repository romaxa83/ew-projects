<?php

namespace App\Console\Commands\Audit;

use App\Models\Audit;
use App\Models\Twilio\TwilioSms;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Helper\ProgressBar;

class ChangeMorphName extends Command
{
    protected $signature = 'audit:change_morph_name';

    public function handle()
    {
        $chunk = 500;

        $query = Audit::query()
            ->where('auditable_type','App\Models\Twilio\TwilioSms')
        ;

        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk) {
                $progressBar->advance($chunk);
                $items->each(function (Audit $item) use ($progressBar, $chunk) {
                    $item->update(['auditable_type' => TwilioSms::MORPH_NAME]);
                });
            });

        } catch (\Throwable $e) {
//            $progressBar->clear();
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
    }
}


