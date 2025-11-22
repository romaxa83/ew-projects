<?php

namespace App\Services\Fax\Drivers\ClickSend;

use ClickSend\Api\FAXApi;
use ClickSend\ApiException;
use ClickSend\Configuration;
use ClickSend\Model\FaxMessageCollection;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Log;
use Throwable;

class ClickSendFaxService
{

    private FAXApi $api;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration()
            ->setUsername(config('clicksend.username'))
            ->setPassword(config('clicksend.token'));

        $this->api = new FaxApi(new Client(), $config);
    }

    /**
     * @param string $id
     * @return ClickSendFaxHistory|null
     */
    public function findById(string $id): ?ClickSendFaxHistory
    {
        return $this->getWeekHistory()
            ->keyBy(fn(ClickSendFaxHistory $message) => $message->getMessageId())
            ->get($id);
    }

    /**
     * @return Collection|ClickSendFaxHistory[]
     */
    public function getWeekHistory(): Collection
    {
        return $this->getHistory(now()->subDays(10)->getTimestamp(), now()->getTimestamp());
    }

    /**
     * @param int $from
     * @param int $to
     * @return Collection|ClickSendFaxHistory[]
     */
    public function getHistory(int $from, int $to): Collection
    {
        try {
            $list = $this->api->faxHistoryGet($from, $to, null, null, 1, 100);

            ['data' => ['data' => $data]] = json_to_array($list);

            return collect($data)
                ->map(fn(array $item) => new ClickSendFaxHistory($item));
        } catch (Throwable $exception) {
            Log::error($exception);
        }

        return collect();
    }

    /**
     * @param FaxMessageCollection $messageCollection
     * @return string
     * @throws ApiException
     */
    public function faxSendPost(FaxMessageCollection $messageCollection): string
    {
        return $this->api->faxSendPost($messageCollection);
    }
}
