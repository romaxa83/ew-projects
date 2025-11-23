<?php

namespace App\Console\Commands\Sdek;

use Illuminate\Console\Command;
use Throwable;
use WezomCms\Core\Models\Setting;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Orders\Services\SdekService;

class SdekWebhookSubscribe extends Command
{
    protected $signature = 'sdek:webhook-subscribe';

    protected $description = 'Подписка на вебхуки СДЕК';

    public function handle()
    {
        try {
            $sdekService = resolve(SdekService::class);
            $webhookUuid = $sdekService->webhookSubscribe();

            $columns = [
                'module' => 'deliveries',
                'group' => 'site',
                'key' => 'sdek_order_webhook_uuid',
                'type' => AbstractField::TYPE_INPUT,
            ];

            $setting = Setting::firstOrCreate($columns);
            $setting->translations()->delete();

            $values = [];

            foreach (array_keys(app('locales')) as $locale) {
                $values[$locale]['value'] = $webhookUuid;
            }
            $setting->update($values);

            $this->info('Subscribed on ORDER_STATUS webhook!');
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
