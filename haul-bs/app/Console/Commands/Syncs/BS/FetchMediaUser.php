<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Foundations\Helpers\DbConnections;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Users\User;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchMediaUser extends BaseCommand
{
    protected $signature = 'sync:bs_user_media';

    public function exec(): void
    {
        echo "[x] START... fetch user media" . PHP_EOL;

        $users = User::query()
            ->whereNotNull('origin_id')
            ->get()
            ->pluck('origin_id', 'id')
            ->toArray()
        ;

        try {
            $data = DbConnections::haulk()
                ->table('media')
                ->where('model_type', 'App\Models\Users\User')
                ->where('collection_name', 'photo')
                ->whereIn('model_id', $users)
                ->get()
                ->toArray();

            $progressBar = new ProgressBar($this->output, count($data));
            $progressBar->setFormat('verbose');
            $progressBar->start();

            foreach ($data as $k => $item) {

                $props = json_to_array($item->custom_properties);
                $conversion = $props['generated_conversions'] ?? [];

                $media = new Media();
                $media->model_type = User::MORPH_NAME;
                $media->model_id = array_flip($users)[$item->model_id];
                $media->collection_name = $item->collection_name;
                $media->name = $item->name;
                $media->file_name = $item->file_name;
                $media->mime_type = $item->mime_type;
                $media->disk = $item->disk;
                $media->conversions_disk = $item->disk;
                $media->size = $item->size;
                $media->manipulations = json_to_array($item->manipulations);
                $media->custom_properties = json_to_array($item->custom_properties);
                $media->responsive_images = json_to_array($item->responsive_images);
                $media->generated_conversions = $conversion;
                $media->order_column = $item->order_column;
                $media->created_at = $item->created_at;
                $media->updated_at = $item->updated_at;
                $media->origin_id = $item->id;

                $media->save();

                $progressBar->advance();
            }

            $progressBar->finish();
            echo PHP_EOL;
            echo "[x]  DONE fetch customers" . PHP_EOL;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}


