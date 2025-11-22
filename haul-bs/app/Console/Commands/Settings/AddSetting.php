<?php

namespace App\Console\Commands\Settings;

use App\Enums\Orders\Parts\DeliveryStatus;
use App\Enums\Orders\Parts\OrderStatus;
use App\Events\Events\Settings\RequestToEcom;
use App\Models\Orders\Parts\Order;
use App\Models\Settings\Settings;
use App\Repositories\Settings\SettingRepository;
use App\Services\Orders\Parts\DeliveryService;
use App\Services\Orders\Parts\OrderStatusService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Throwable;

class AddSetting extends Command
{
    protected $signature = 'settings:add';
    protected $description = 'Добавление настроек';

    protected array $settings = [
        'ecommerce_billing_payment_options' => null
    ];

    public function __construct(protected SettingRepository $repo) {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $this->addSetting();
        } catch (Throwable $e) {
            dd($e);
        }

        return self::SUCCESS;
    }

    public function addSetting()
    {
        $settings = $this->repo->getInfo();

        foreach ($this->settings as $setting => $value){

            if(!$settings->has($setting)){

                if($setting == 'ecommerce_billing_payment_options'){
                    $value = sprintf(
                        "If you have any questions concerning this invoice, contact %s, %s or email us at %s",
                        $settings['ecommerce_phone_name']?->value ?? null,
                        $settings['ecommerce_phone']?->value ?? null,
                        $settings['ecommerce_email']?->value ?? null,
                    );
                }

                $model = new Settings();
                $model->name = $setting;
                $model->value = $value;
                $model->save();
            }

        }

        event(new RequestToEcom($this->repo->getInfoForEcomm()));
    }
}

