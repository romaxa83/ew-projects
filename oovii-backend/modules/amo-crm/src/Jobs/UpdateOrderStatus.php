<?php

namespace WezomCms\AmoCrm\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use WezomCms\AmoCrm\Services\AmoCrmService;
use WezomCms\Orders\Models\Order;

class UpdateOrderStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Order
     */
    protected $order;

    /**
     * Create a new job instance.
     *
     * @param $order
     */
    public function __construct($order)
    {
        $this->order = $order;

        $this->onQueue(config('cms.amo-crm.amo-crm.queue'));
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     * @throws \AmoCRM\Exceptions\AmoCRMApiException
     * @throws \AmoCRM\Exceptions\AmoCRMoAuthApiException
     * @throws \AmoCRM\Exceptions\InvalidArgumentException
     */
    public function handle()
    {
        if ($this->order->amocrm_lead_id) {
            /** @var AmoCrmService $service */
            $service = resolve(AmoCrmService::class);

            $lead = $service->getLeadById($this->order->amocrm_lead_id);

            if ($lead && !empty($this->order->status->amocrm_value_id)) {
                $service->updateLeadStausId($lead, $this->order->status->amocrm_value_id);
            }
        }
    }
}
