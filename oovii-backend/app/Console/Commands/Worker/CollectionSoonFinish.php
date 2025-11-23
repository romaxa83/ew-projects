<?php

namespace App\Console\Commands\Worker;

use Illuminate\Console\Command;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Repositories\CollectionRepository;
use WezomCms\Firebase\Events\FcmGroupPush;
use WezomCms\Firebase\Models\Template;
use WezomCms\TelegramBot\Telegram;

class CollectionSoonFinish extends Command
{
    protected $signature = 'cmd:worker:collection-soon-finish';

    protected $description = 'Обработка коллекций, которые скоро финишируют';

    protected $hour = 48;

    public function handle(CollectionRepository $repo)
    {
        $models = $repo->collectionSoonFinish($this->hour);
        foreach ($models as $model){
            /** @var $model Collection */
            event(new FcmGroupPush(Template::TYPE_COLLECTION_SOON_FINISH, $model));
            $model->update([
                'is_send_finish' => true,
            ]);
        }

        if($models->isNotEmpty()){
            Telegram::info("🏁 SOON FINISH collections [{$models->count()}]", 'SYSTEM',Telegram::LEVEL_IMPORTANT);
        }
    }
}
