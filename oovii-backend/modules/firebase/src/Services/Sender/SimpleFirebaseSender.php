<?php

namespace WezomCms\Firebase\Services\Sender;

use WezomCms\Firebase\Exceptions\FcmPushException;
use WezomCms\Firebase\Messages\FcmPayload;
use Illuminate\Support\Facades\Http;

class SimpleFirebaseSender implements FirebaseSender
{
    private string $url;
    private string $serverKey;

    public function __construct(string $url, string $serverKey)
    {
        $this->url = $url;
        $this->serverKey = $serverKey;
    }

    public function send(FcmPayload $data)
    {
        $fields = [
            'to' => $data->getFcmToken(),
            'notification' => [
                'title' => $data->getMsgPayload()->getTitle(),
                'body' => $data->getMsgPayload()->getText(),
            ],
            'data' => [
                'type' => $data->getMsgPayload()->getType(),
                'additional' => $data->getAdditional()
            ]
        ];

        try {
            $res = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])
                ->withoutVerifying()
                ->post($this->url, $fields);

            if($res->json('success') === 0){
                throw new FcmPushException($res->json('results.0.error'));
            }

            return $res->json();
        } catch (\Throwable $e){
            logger($e->getMessage());
            throw new FcmPushException($e->getMessage());
        }
    }
}
