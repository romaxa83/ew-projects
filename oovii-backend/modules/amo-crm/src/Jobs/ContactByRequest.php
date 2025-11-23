<?php

namespace WezomCms\AmoCrm\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use WezomCms\AmoCrm\Services\AmoCrmService;

class ContactByRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $elementId;

    /**
     * @var string
     */
    protected $elementType;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var int|null
     */
    protected $responsibleUserId;

    /**
     * Create a new job instance.
     *
     * @param  int  $elementId
     * @param  string  $elementType
     * @param  string  $text
     * @param  int|null  $responsibleUserId
     */
    public function __construct(int $elementId, string $elementType, string $text, int $responsibleUserId = null)
    {
        $this->elementId = $elementId;
        $this->elementType = $elementType;
        $this->text = $text;
        $this->responsibleUserId = $responsibleUserId;

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
        /** @var AmoCrmService $service */
        $service = resolve(AmoCrmService::class);

        $service->addTask($this->elementId, $this->elementType, $this->text, $this->responsibleUserId);
    }
}
