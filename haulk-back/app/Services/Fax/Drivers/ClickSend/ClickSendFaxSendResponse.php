<?php

namespace App\Services\Fax\Drivers\ClickSend;

use App\Services\Fax\Drivers\FaxSendResponse;
use Illuminate\Support\Collection;

class ClickSendFaxSendResponse implements FaxSendResponse
{
    public const RESPONSE_CODE_SUCCESS = 'SUCCESS';

    private array $response;

    /**
     * @var Collection|ClickSendFaxHistory[]
     */
    private Collection $messages;

    private ClickSendFaxHistory $message;

    public function __construct(string $response)
    {
        $this->response = json_to_array($response);

        $this->messages = $this->setMessagesByArray($this->response['data']['messages']);

        $this->message = $this->messages->first();
    }

    /**
     * @param array $messages
     * @return Collection
     */
    protected function setMessagesByArray(array $messages): Collection
    {
        return collect($messages)
            ->map(fn(array $item) => new ClickSendFaxHistory($item));
    }

    public function isSuccess(): bool
    {
        return $this->response['response_code'] === self::RESPONSE_CODE_SUCCESS;
    }

    public function isInQueue(): bool
    {
        return $this->message->isInQueue();
    }

    public function isFail(): bool
    {
        return $this->message->isFailed();
    }

    /**
     * @return ClickSendFaxHistory[]|Collection
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function isSent(): bool
    {
        return $this->message->isSent();
    }

    public function refreshStatuses(): void
    {
        $messageId = $this->message->getMessageId();

        /** @var ClickSendFaxHistory $clickSendFaxHistory */
        $clickSendFaxHistory = $this->getClickSendService()->findById($messageId);

        $this->message = $clickSendFaxHistory;
    }

    protected function getClickSendService(): ClickSendFaxService
    {
        return resolve(ClickSendFaxService::class);
    }

    public function isInvalidRecipient(): bool
    {
        return $this->message->isInvalidRecipient();
    }
}
