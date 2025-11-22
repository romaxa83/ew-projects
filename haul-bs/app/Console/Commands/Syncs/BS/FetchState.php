<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Foundations\Helpers\DbConnections;
use App\Foundations\Modules\Location\Models\City;
use App\Foundations\Modules\Location\Models\State;
use App\Models\Orders\BS\Order;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchState extends BaseCommand
{
    protected $signature = 'sync:bs_fetch_state';

    public function exec(): void
    {
        try {
            if(!State::query()->first()){
                $this->fetchState();
            }

            if(!City::query()->first()){
                echo "[x] START... fetch cities" . PHP_EOL;
                $statesIds = State::query()
                    ->get()
                    ->pluck('id', 'origin_id')
                    ->toArray();


                $progressBar = new ProgressBar($this->output, DbConnections::haulk()->table('cities')->count());
                $progressBar->setFormat('verbose');
                $progressBar->start();

                DbConnections::haulk()
                    ->table('cities')
                    ->orderBy('id')
                    ->chunk(2000, function ($data) use ($statesIds, $progressBar) {
                        $tmp = [];
                        foreach ($data as $k => $item){
                            $item = std_to_array($item);

                            $tmp[$k] = [
                                'name' => $item['name'],
                                'zip' => $item['zip'],
                                'active' => $item['status'],
                                'timezone' => $item['timezone'],
                                'country_code' => $item['country_code'],
                                'country_name' => $item['country_name'],
                                'state_id' => $statesIds[$item['state_id']],
                            ];

//                            dd($tmp);

//                            $m = new City();
//                            $m->name = $item['name'];
//                            $m->zip = $item['zip'];
//                            $m->active = $item['status'];
//                            $m->timezone = $item['timezone'];
//                            $m->country_code = $item['country_code'];
//                            $m->country_name = $item['country_name'];
//                            $m->state_id = $statesIds[$item['state_id']];
//                            $m->save();
                        }
                        City::query()->insert($tmp);
                        $progressBar->advance(2000);
                    })
                ;

                $progressBar->finish();

            }

        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }

    public function fetchState(): void
    {
        $data = DbConnections::haulk()
            ->table('states')
            ->get()
            ->toArray()
        ;

        foreach ($data as $k => $item){
            $item = std_to_array($item);
            $item['active'] = $item['status'];
            $item['origin_id'] = $item['id'];
            unset(
                $item['created_at'],
                $item['updated_at'],
                $item['status'],
            );
            $data[$k] = $item;
        }

        \DB::table(State::TABLE)->insert($data);

        $this->info('Fetch states');
    }
}
