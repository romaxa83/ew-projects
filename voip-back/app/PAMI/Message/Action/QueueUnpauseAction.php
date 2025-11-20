<?php

namespace App\PAMI\Message\Action;

/**
 * Queue unpause action. This does not exist in the ami.
 */
class QueueUnpauseAction extends ActionMessage
{
    public function __construct($interface, $queue = false, $reason = false)
    {
        parent::__construct('QueuePause');
        if ($queue !== false) {
            $this->setKey('Queue', $queue);
        }
        if ($reason !== false) {
            $this->setKey('Reason', $reason);
        }
        $this->setKey('Interface', $interface);
        $this->setKey('Paused', 'false');
    }
}
