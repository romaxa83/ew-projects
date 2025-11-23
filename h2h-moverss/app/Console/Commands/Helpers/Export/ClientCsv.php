<?php

namespace App\Console\Commands\Helpers\Export;

use App\Models\Client;
use App\Models\Division;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\Console\Helper\ProgressBar;

class ClientCsv extends Command
{
    protected $signature = 'helpers:export-client';

    public function handle()
    {
        $date = CarbonImmutable::now()->startOfYear();

        foreach (Division::query()->get() as $division) {
            $clientsIds = Order::query()
                ->select('client_id')
                ->where('division_id', $division->id)
                ->where('client_id', '!=', 0)
                ->whereDate('created_at', '>=', $date)
                ->groupBy('client_id')
                ->get()
                ->pluck('client_id')
                ->toArray()
            ;

            $clientQuery = Client::query()
                ->whereIn('id', $clientsIds)
                ->whereDate('created_at', '>=', $date);

            $this->exec($clientQuery, $division->name . '_clients_' . $date->format('Y'));
        }
    }

    public function exec(Builder $clientQuery, string $fileName)
    {
        $filename = $fileName.'.csv';
        $file = fopen($filename, 'w');
        fputcsv($file, [
            'ID',
            'Month',
            'Name',
            'Phone',
            'Email',
        ]);

        $date = CarbonImmutable::now()->startOfYear();

        $progressBar = new ProgressBar($this->output, $clientQuery->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();

        try {
            $clientQuery->with(['phones', 'emails'])
                ->chunk(100, function (Collection $items) use ($file, $progressBar) {

                    $items->each(function (Client $client) use ($file, $progressBar) {
                        $phones = $client->phones->pluck('value')->toArray();
                        $emails = $client->emails->pluck('value')->toArray();

                        $tmp = [];
                        if(count($phones) > count($emails)) {
                            foreach ($phones as $k => $phone) {
                                if($k == 0){
                                    $tmp[$k] = [
                                        'id' => $client->id,
                                        'month' => $client->created_at->format('F'),
                                        'name' => $client->fullname,
                                    ];
                                } else {
                                    $tmp[$k] = [
                                        'id' => null,
                                        'month' => null,
                                        'name' => null,
                                    ];
                                }

                                $tmp[$k]['phone'] = $phone;
                                $tmp[$k]['email'] = $emails[$k] ?? null;
                            }
                        } elseif(count($phones) < count($emails)) {
                            foreach ($emails as $k => $email) {
                                if($k == 0){
                                    $tmp[$k] = [
                                        'id' => $client->id,
                                        'month' => $client->created_at->format('F'),
                                        'name' => $client->fullname,
                                    ];
                                } else {
                                    $tmp[$k] = [
                                        'id' => null,
                                        'month' => null,
                                        'name' => null,
                                    ];
                                }

                                $tmp[$k]['phone'] = $phones[$k] ?? null;
                                $tmp[$k]['email'] = $email;
                            }
                        } else {
                            if(count($phones) == 0) {
                                $tmp[0] = [
                                    'id' => $client->id,
                                    'month' => $client->created_at->format('F'),
                                    'name' => $client->fullname,
                                    'phone' => null,
                                    'email' => null,
                                ];
                            } else {
                                foreach ($emails as $k => $email) {
                                    if($k == 0){
                                        $tmp[$k] = [
                                            'id' => $client->id,
                                            'month' => $client->created_at->format('F'),
                                            'name' => $client->fullname,
                                        ];
                                    } else {
                                        $tmp[$k] = [
                                            'id' => null,
                                            'month' => null,
                                            'name' => null,
                                        ];
                                    }

                                    $tmp[$k]['phone'] = $phones[$k] ?? null;
                                    $tmp[$k]['email'] = $email;
                                }
                            }
                        }
dd($tmp);
                        foreach ($tmp as $row) {
                            fputcsv($file, $row);
                        }

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
