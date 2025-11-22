<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Foundations\Helpers\DbConnections;
use App\Foundations\Modules\Localization\Enums\Translations\TranslationPlace;
use App\Foundations\Modules\Localization\Models\Translation;
use Carbon\CarbonImmutable;

class FetchTranslations extends BaseCommand
{
    protected $signature = 'sync:bs_translations';

    public function exec(): void
    {
        $now = CarbonImmutable::now();

        try {
            $data = DbConnections::haulk()
                ->table('translates')
                ->join(
                    'translates_translates',
                    'translates.id',
                    '=',
                    'translates_translates.row_id'
                )
                ->select('translates.key', 'translates_translates.text', 'translates_translates.language')
                ->get()
                ->toArray()
            ;

            $grouped = [];
            foreach ($data as $item) {
                $item = std_to_array($item);
                if(
                    !str_contains($item['key'], 'gps')
                    && !str_contains($item['key'], 'GPS')
                    && !str_contains($item['key'], 'Gps')
                    && !str_contains($item['key'], 'fuel')
                    && !str_contains($item['key'], 'Fuel')
                    && !str_contains($item['key'], 'device')
                    && !str_contains($item['key'], 'event-name')
                    && !str_contains($item['key'], 'driver-activity')
                ){
                    $key = $item['key'];
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = [];
                    }
                    $grouped[$key][] = $item;
                }
            }

            $insert = [];
            foreach ($grouped as $item){
                foreach ($item as $i){
                    $insert[] = [
                        'place' => TranslationPlace::SITE,
                        'key' => $i['key'],
                        'text' => $i['text'],
                        'lang' => $i['language'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            \DB::table(Translation::TABLE)->insert($insert);

            $this->info('Fetch translations');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
