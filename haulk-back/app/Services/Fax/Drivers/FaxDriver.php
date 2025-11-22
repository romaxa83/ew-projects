<?php

namespace App\Services\Fax\Drivers;

use Illuminate\Foundation\Application;

interface FaxDriver
{
    public static function create(Application $application): self;

    public function send(string $to, string $from, string $fileUrl): FaxSendResponse;
}
