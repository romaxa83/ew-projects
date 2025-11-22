<?php

namespace App\Services\Fax\Drivers\ClickSend;

use App\Services\Fax\Drivers\FaxDriver;
use App\Services\Fax\Drivers\FaxSendResponse;
use ClickSend\Model\FaxMessage;
use ClickSend\Model\FaxMessageCollection;
use Exception;
use Illuminate\Foundation\Application;
use Log;

class ClickSendFaxDriver implements FaxDriver
{

    private ClickSendFaxService $service;

    private function __construct()
    {
    }

    public static function create(Application $application): FaxDriver
    {
        $self = new self();
        $self->service = $application->make(ClickSendFaxService::class);

        return $self;
    }

    /**
     * @param string $to
     * @param string $from
     * @param string $fileUrl
     * @return FaxSendResponse
     * @throws Exception
     */
    public function send(string $to, string $from, string $fileUrl): FaxSendResponse
    {
        $message = (new FaxMessage())
            ->setFrom($from)
            ->setTo($to);

        $messageCollection = (new FaxMessageCollection())
            ->setMessages([$message])
            ->setFileUrl($fileUrl);

        try {
            $result = $this->service->faxSendPost($messageCollection);

            if (config('app.debug')) {
                Log::info($result);
            }

            return new ClickSendFaxSendResponse($result);
        } catch (Exception $exception) {
            Log::error($exception);

            throw new Exception('Fax send error: ' . $exception->getMessage());
        }
    }

}
