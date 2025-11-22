<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Enums\Tags\TagType;
use App\Foundations\Helpers\DbConnections;
use App\Models\Tags\Tag;

class FetchTag extends BaseCommand
{
    protected $signature = 'sync:bs_tag';

    public function exec(): void
    {
        try {
            $this->info('Fetch tags');

            $data = DbConnections::haulk()
                ->table('tags')
                ->whereIn('type',['vehicle_owner', 'trucks_and_trailer'])
                ->get()
                ->toArray()
            ;

            foreach ($data as $k => $item){
                $item = std_to_array($item);
                $item['origin_id'] = $item['id'];
                if($item['type'] == 'vehicle_owner'){
                    $item['type'] = TagType::CUSTOMER;
                }
                unset(
                    $item['id'],
                    $item['broker_id'],
                    $item['carrier_id'],
                );
                $data[$k] = $item;
            }

            \DB::table(Tag::TABLE)->upsert($data, ['origin_id']);

            $this->info('Fetch tags');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
