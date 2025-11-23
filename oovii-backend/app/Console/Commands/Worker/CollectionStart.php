<?php

namespace App\Console\Commands\Worker;

use Illuminate\Console\Command;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Repositories\CollectionRepository;
use WezomCms\Firebase\Events\FcmGroupPush;
use WezomCms\Firebase\Models\Template;
use WezomCms\TelegramBot\Telegram;

class CollectionStart extends Command
{
    protected $signature = 'cmd:worker:collection-start';

    protected $description = 'Обработка коллекций, которые стартонули';

    public function handle(CollectionRepository $repo)
    {
        $models = $repo->collectionStart();
//dd($models);
        foreach ($models as $model){
            /** @var $model Collection */
            event(new FcmGroupPush(Template::TYPE_COLLECTION_START, $model));
            $model->update(['is_send_start' => true]);
        }

        if($models->isNotEmpty()){
            Telegram::info("🗑 START collections [{$models->count()}]", 'SYSTEM',Telegram::LEVEL_IMPORTANT);
        }
    }
}
