<?php

namespace App\Jobs\Fax;

use App\Services\Fax\Drivers\FaxDriver;
use App\Services\Fax\Drivers\FaxProcess;
use App\Services\Fax\Drivers\PreparedFaxMessage;
use App\Services\Fax\Handlers\StatusHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Queue;

class SendFaxJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const CREATED = 'created';

    public const FAIL = 'fail';

    public const IN_QUEUE = 'in_queue';

    private string $status;

    private PreparedFaxMessage $message;

    private FaxProcess $process;

    private StatusHandler $statusHandler;

    public function __construct(
        PreparedFaxMessage $message,
        StatusHandler $statusHandler,
        FaxProcess $process,
        string $status = self::CREATED
    ) {
        $this->message = $message;
        $this->statusHandler = $statusHandler;
        $this->process = $process;
        $this->status = $status;
    }

    public function handle()
    {
        $message = $this->message;

        if ($this->status === self::CREATED || $this->status === self::FAIL) {
            $response = $this->getFaxDriver()
                ->send(
                    $message->getTo(),
                    $message->getFrom(),
                    $message->getFileUrl()
                );

            if ($response->isInvalidRecipient()) {
                $this->statusHandler->afterFail();
                return;
            }

            $this->process->setResponse($response);
        }

        $process = $this->process;
        $response = $process->getResponse();
        $response->refreshStatuses();

        if ($response->isInQueue()) {
            $process->inQueue();

            if ($process->hasQueueAttempts()) {
                $this->dispatchLater($process->getHoldAfterInQueue(), self::IN_QUEUE);
                return;
            }

            $this->statusHandler->afterFail();
            return;
        }

        if ($response->isSent()) {
            $this->statusHandler->afterSuccess();
            return;
        }

        if ($response->isFail()) {
            $process->fail();

            if ($process->hasFailAttempts()) {
                $this->dispatchLater($process->getHoldAfterFail(), self::FAIL);
                return;
            }

            $this->statusHandler->afterFail();
            return;
        }

        $this->statusHandler->afterFail();
    }

    protected function getFaxDriver(): FaxDriver
    {
        return resolve(FaxDriver::class);
    }

    protected function dispatchLater(int $date, string $status): void
    {
        Queue::later(
            $date,
            new self($this->message, $this->statusHandler, $this->process, $status)
        );
    }
}
