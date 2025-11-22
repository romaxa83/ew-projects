<?php

namespace App\Services\Fax;

use App\Services\Fax\Handlers\StatusHandler;

class DefaultStatusHandler implements StatusHandler
{
    public function afterFail()
    {
    }

    public function afterSuccess()
    {
    }

    public function setMessage($message): StatusHandler
    {
        return $this;
    }

    public function setFileName(string $fileName): StatusHandler
    {
        return $this;
    }
}
