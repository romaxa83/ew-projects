<?php

namespace App\Console\Commands\Helpers\Export;

use App\Models\Audit;
use App\Providers\AppServiceProvider;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Helper\ProgressBar;

class AuditCsv extends Command
{
    protected $signature = 'helpers:export-audit';

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            echo PHP_EOL;
            $this->info("Done [time = {$time}]");
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    public function exec()
    {
        $start = CarbonImmutable::createFromFormat('Y-m-d', '2025-04-28')
            ->startOfDay();
        $end = CarbonImmutable::createFromFormat('Y-m-d', '2025-05-04')
            ->endOfDay();

        $query = Audit::query()
            ->whereBetween('created_at', [$start, $end])
            ;

        $filename = 'audit_2025-04-28_2025-05-04.csv';
        $file = fopen($filename, 'w');
        fputcsv($file, [
            'ID',
            'Date',
            'Author',
            'OrderID',
            'Object',
            'Old Value',
            'New value',
        ]);

        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();

        try {
            $query->with(['user'])
                ->chunk(10, function (Collection $items) use ($file, $progressBar) {

                    $items->each(function (Audit $model) use ($file, $progressBar) {
                        $tmp = [
                            'id' => $model->id,
                            'date' => $model->created_at->format('Y-m-d H:i:s'),
                            'author' => $model->user?->name,
                            'order_id' => $model->order_id,
                            'object' => isset(AppServiceProvider::morphs()[$model->auditable_type])
                                ? AppServiceProvider::morphs()[$model->auditable_type]
                                : $model->auditable_type,
                            'old_value' => array_to_json($model->old_values),
                            'new_value' => array_to_json($model->new_values),
                        ];

                        fputcsv($file, $tmp);

                        $progressBar->advance();

                    });
                })
            ;
        }catch (\Exception $e){
            fclose($file);
            dd($e);
        }

        fclose($file);

        echo PHP_EOL;

        return self::SUCCESS;
    }
}

