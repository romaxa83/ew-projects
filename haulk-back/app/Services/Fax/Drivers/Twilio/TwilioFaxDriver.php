<?php

namespace App\Services\Fax\Drivers\Twilio;

use App\Services\Fax\Drivers\FaxDriver;
use App\Services\Fax\Drivers\FaxSendResponse;
use Exception;
use Illuminate\Foundation\Application;
use Log;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Twilio\Rest\Fax\V1\FaxList;

class TwilioFaxDriver implements FaxDriver
{
    private Client $client;

    private function __construct()
    {
    }

    /**
     * @param Application $application
     * @return FaxDriver
     * @throws ConfigurationException
     */
    public static function create(Application $application): FaxDriver
    {
        $self = new self();

        $self->client = new Client(
            config('twilio.auth.sid'),
            config('twilio.auth.token')
        );

        return $self;
    }

    /**
     * @param string $to
     * @param string $from
     * @param string $fileUrl
     * @return FaxSendResponse
     * @throws TwilioException
     */
    public function send(string $to, string $from, string $fileUrl): FaxSendResponse
    {
        try {
            $this->getFaxClient()->create($to, $fileUrl, ['from' => $from]);

            return new TwilioFaxSendResponse();
        } catch (Exception $exception) {
            Log::error($exception);
            throw $exception;
        }
    }

    public function getFaxClient(): FaxList
    {
        return $this->client->fax->v1->faxes;
    }
}
