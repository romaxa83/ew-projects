<?php

namespace App\Console\Commands\Sdek;

use Illuminate\Console\Command;
use Log;
use Throwable;
use WezomCms\Core\Models\Setting;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Orders\Services\SdekService;

class SdekWebhookUnsubscribe extends Command
{
    protected $signature = 'sdek:webhook-unsubscribe';

    protected $description = 'Отписаться от вебхука СДЕК';

    public function handle()
    {
        try {
            $columns = [
                'module' => 'deliveries',
                'group' => 'site',
                'key' => 'sdek_order_webhook_uuid',
                'type' => AbstractField::TYPE_INPUT,
            ];
            $webhookUuid = settings('deliveries.site.sdek_order_webhook_uuid');

            if ($webhookUuid) {
                $sdekService = resolve(SdekService::class);
                if ($sdekService->webhookUnsubscribe($webhookUuid)) {
                    Setting::where($columns)->delete();
                }
            }

            $this->info('You have been unsubscribed from ORDER_STATUS webhook!');
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
