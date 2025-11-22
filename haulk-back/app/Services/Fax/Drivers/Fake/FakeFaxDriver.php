<?php

namespace App\Services\Fax\Drivers\Fake;

use App\Services\Fax\Drivers\FaxDriver;
use App\Services\Fax\Drivers\FaxSendResponse;
use Illuminate\Foundation\Application;

class FakeFaxDriver implements FaxDriver
{
    private static array $sent = [];

    private function __construct()
    {
    }

    public static function create(Application $application): FaxDriver
    {
        self::$sent = [];

        return new self();
    }

    public static function getSent(): array
    {
        return self::$sent;
    }

    public static function clear(): void
    {
        self::$sent = [];
    }

    public function send(string $to, string $from, string $fileUrl): FaxSendResponse
    {
        self::$sent[] = [
            'to' => $to,
            'from' => $from,
            'fileUrl' => $fileUrl,
        ];

        return new FakeFaxSendResponse();
    }
}
