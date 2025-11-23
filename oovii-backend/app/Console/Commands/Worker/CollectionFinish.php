<?php

namespace App\Console\Commands\Worker;

use Illuminate\Console\Command;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Repositories\CollectionRepository;
use WezomCms\TelegramBot\Telegram;

class CollectionFinish extends Command
{
    protected $signature = 'cmd:worker:collection-finish';

    protected $description = 'Обработка коллекций, которые финишировали';

    public function handle(CollectionRepository $repo)
    {
        $models = $repo->collectionFinish();
        foreach ($models as $model){
            /** @var $model Collection */
            $model->update([
                'is_send_start' => false,
                'is_send_finish' => false,
                'published' => false,
            ]);
        }

        if($models->isNotEmpty()){
            Telegram::info("🏁 FINISH collections [{$models->count()}]", 'SYSTEM',Telegram::LEVEL_IMPORTANT);
        }
    }
}
