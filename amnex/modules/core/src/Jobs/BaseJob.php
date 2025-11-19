<?php

declare(strict_types=1);

namespace Wezom\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const QUEUE = 'default';

    public function __construct()
    {
        $this->onQueue($this->getQueueName());
    }

    protected function getQueueName(): string
    {
        return static::QUEUE;
    }
}
