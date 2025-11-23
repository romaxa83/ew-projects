<?php

namespace WezomCms\AmoCrm\Jobs;

use AmoCRM\Collections\LinksCollection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use WezomCms\AmoCrm\Services\AmoCrmService;

class CreateLink implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var int
     */
    protected $fromId;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var int
     */
    protected $toId;

    /**
     * @var array
     */
    protected $payload;

    /**
     * Create a new job instance.
     *
     * @param  string  $from
     * @param  int  $fromId
     * @param  string  $to
     * @param  int  $toId
     * @param  array  $payload
     */
    public function __construct(string $from, int $fromId, string $to, int $toId, array $payload = [])
    {
        $this->from = $from;
        $this->fromId = $fromId;
        $this->to = $to;
        $this->toId = $toId;
        $this->payload = $payload;

        $this->onQueue(config('cms.amo-crm.amo-crm.queue'));
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \AmoCRM\Exceptions\AmoCRMApiException
     * @throws \AmoCRM\Exceptions\AmoCRMoAuthApiException
     * @throws \AmoCRM\Exceptions\InvalidArgumentException
     */
    public function handle()
    {
        /** @var AmoCrmService $service */
        $service = resolve(AmoCrmService::class);

        //TODO: Refactoring
        $to = null;
        if ($this->to == 'contact') {
            $to = $service->getContactById($this->toId);
            $to->setUpdatedBy(null);
        }
//        if ($this->to == 'product') {
//            $to = $service->getProductById($this->toId);
//            $to->setQuantity($this->payload['quantity'] ?: 1);
//        }

        if ($this->from == 'lead' && $to) {
            if ($lead = $service->getLeadById($this->fromId)) {
                $service->linkToLead($lead, (new LinksCollection)->add($to));
            }
        }
    }
}
