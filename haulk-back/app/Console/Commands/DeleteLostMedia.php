<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\Models\Media;

class DeleteLostMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:delete-lost';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete media that is not connected to any model';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $models = Media::select('model_type')
            ->groupBy('model_type')
            ->pluck('model_type')
            ->all();

        if ($models) {
            foreach ($models as $model) {
                $media = Media::whereDoesntHaveMorph(
                    'model',
                    $model,
                    function (Builder $query) {
                        $query->withoutGlobalScopes();
                    }
                )->limit(
                    config('saas.system.delete_media_batch_size')
                )->get();

                if ($media->count()) {
                    $media->each->delete();
                    return 0;
                }
            }
        }

        return 0;
    }
}
