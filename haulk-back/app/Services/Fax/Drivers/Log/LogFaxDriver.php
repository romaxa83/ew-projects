<?php

namespace App\Services\Fax\Drivers\Log;

use App\Services\Fax\Drivers\FaxDriver;
use App\Services\Fax\Drivers\FaxSendResponse;
use Illuminate\Foundation\Application;
use Log;

class LogFaxDriver implements FaxDriver
{

    public static function create(Application $application): FaxDriver
    {
        return new self();
    }

    public function send(string $to, string $from, string $fileUrl): FaxSendResponse
    {
        Log::info(
            sprintf(
                'Send new fax from: %s, to: %s. FilePath: %s',
                $from,
                $to,
                $fileUrl
            )
        );

        return new LogFaxSendResponse();
    }
}
