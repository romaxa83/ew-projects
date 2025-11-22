<?php

namespace App\Services\Fax\Handlers;

interface StatusHandler
{
    public function afterFail();

    public function afterSuccess();

    public function setMessage($message): self;

    public function setFileName(string $fileName): self;
}
