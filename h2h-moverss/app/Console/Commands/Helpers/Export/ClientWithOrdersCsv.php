<?php

namespace App\Console\Commands\Helpers\Export;

use App\Models\Division;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\Console\Helper\ProgressBar;

class ClientWithOrdersCsv extends Command
{
    protected $signature = 'helpers:export-orders-csv';

    public function handle()
    {
        $start = '2024-04-01';
        $end = '2024-12-31';

        foreach (Division::query()->get() as $division) {
            $startPeriod = CarbonImmutable::createFromFormat('Y-m-d', $start, $division['miscs']['tz'])
                ->startOfDay()
                ->setTimezone(new \DateTimeZone('UTC'))
            ;
            $endPeriod = CarbonImmutable::createFromFormat('Y-m-d', $end, $division['miscs']['tz'])
                ->endOfDay()
                ->setTimezone(new \DateTimeZone('UTC'))
            ;

            $orderBuilder = Order::query()
                ->with([
                    'client',
                    'client.phones',
                    'client.emails'
                ])
                ->whereIn('status_id', [
                    Order\Status::LOST_ID,
                    Order\Status::BOOKED_ID,
                    Order\Status::SALES_DONE_ID,
                ])
                ->where('division_id', $division->id)
                ->whereBetween('created_at', [$startPeriod, $endPeriod])
                ->orderBy('created_at')
            ;

            $this->exec($orderBuilder, $division->name . '_orders_with_client');
        }
    }

    public function exec(Builder $builder, string $fileName)
    {
        $filename = $fileName.'.csv';
        $file = fopen($filename, 'w');
        fputcsv($file, [
            'ID Order',
            'Status',
            'Month',
            'Name',
            'Phone',
            'Email',
        ]);

        $progressBar = new ProgressBar($this->output, $builder->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();

        try {
            $builder
                ->chunk(3, function (Collection $items) use ($file, $progressBar) {

                    $items->each(function (Order $model) use ($file, $progressBar) {

                        try {
                            $tmp = [
                                'order_id' => $model->id,
                                'status' => $this->status($model->status_id),
                                'month' => $model->created_at->format('F'),
                                'name' => !is_null($model->client)
                                    ? $model->client->fullname
                                    : '',
                                'phone' => !is_null($model->client)
                                    ? implode(',', $model->client->phones->pluck('value')->toArray())
                                    : "",
                                'email' => !is_null($model->client)
                                    ? implode(',', $model->client->emails->pluck('value')->toArray())
                                    : "",
                            ];
                        } catch (\Throwable $e) {
                            dd($model);
                        }



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

    private function status(int $status): int|string
    {
        if($status === Order\Status::BOOKED_ID){
            return "Booked";
        }
        if($status === Order\Status::SALES_DONE_ID){
            return "Sales Done";
        }
        if($status === Order\Status::LOST_ID){
            return "Lost";
        }

        return $status;
    }
}
