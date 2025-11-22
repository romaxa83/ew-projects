<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Foundations\Helpers\DbConnections;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Settings\Settings;

class FetchSettings extends BaseCommand
{
    protected $signature = 'sync:bs_settings';

    public function exec(): void
    {
        try {
            $data = DbConnections::haulk()
                ->table('bs_settings')
                ->get()
                ->toArray()
            ;

            $file = DbConnections::haulk()
                ->table('media')
                ->where('model_type', 'App\Models\BodyShop\Settings\Settings')
                ->first()
            ;

            foreach ($data as $k => $item){
                $item = std_to_array($item);
                unset(
                    $item['id'],
                );
                $data[$k] = $item;
            }

            foreach ($data as $k => $item){
                if(!Settings::query()->where('name', $item['name'])->exists()){
                    $model = new Settings();
                    $model->name = $item['name'];
                    $model->value = $item['value'];
                    $model->save();
                }
            }

            if($file){
                $logoSettings = Settings::query()->where('name', 'logo')->first();
                if($logoSettings){

                    $props = json_to_array($file->custom_properties);
                    $conversion = $props['generated_conversions'] ?? [];

                    $media = new Media();
                    $media->model_type = Settings::MORPH_NAME;
                    $media->model_id = $logoSettings->id;
                    $media->collection_name = $file->collection_name;
                    $media->name = $file->name;
                    $media->file_name = $file->file_name;
                    $media->mime_type = $file->mime_type;
                    $media->disk = $file->disk;
                    $media->conversions_disk = $file->disk;
                    $media->size = $file->size;
                    $media->manipulations = json_to_array($file->manipulations);
                    $media->custom_properties = json_to_array($file->custom_properties);
                    $media->responsive_images = json_to_array($file->responsive_images);
                    $media->generated_conversions = $conversion;
                    $media->order_column = $file->order_column;
                    $media->created_at = $file->created_at;
                    $media->updated_at = $file->updated_at;
                    $media->origin_id = $file->id;
                    $media->save();
                }
            }


            $this->info('Fetch settings');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}

