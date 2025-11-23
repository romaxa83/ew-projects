<?php

namespace WezomCms\Users\Notifications\Channels;

use Exception;
use Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Notifications\Notification;
use Throwable;
use WezomCms\Users\Exceptions\ESputnikException;

class ESputnikChannel
{
    /**
     * @var PendingRequest
     */
    private $client;

    /**
     * @var string|null
     */
    private $from;

    /**
     * ESputnikChannel constructor.
     * @param  string  $user
     * @param  string  $password
     * @param  string  $from
     */
    public function __construct(string $user, string $password, string $from)
    {
        $this->client = Http::baseUrl('https://esputnik.com.ua/api')
            ->acceptJson()
            ->asJson()
            ->withBasicAuth($user, $password)
            ->timeout(2);

        $this->from = $from;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  Notification  $notification
     * @return void
     * @throws ESputnikException
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            $response = $this->client->post('v1/message/sms', [
                'from' => $this->from,
                'phoneNumbers' => $notifiable->routeNotificationFor('eSputnik'),
                'text' => $notification->toESputnik($notifiable),
            ])->json();

            if (array_get($response, 'results.status') !== 'OK') {
                logger()->error('Bad response', compact('response'));
                throw new Exception('Failed to send SMS');
            }
        } catch (Throwable $e) {
            report($e);
            throw new ESputnikException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
