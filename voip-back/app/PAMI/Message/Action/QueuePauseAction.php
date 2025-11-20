<?php

namespace App\PAMI\Message\Action;

/**
 * Queue pause action.
 */
class QueuePauseAction extends ActionMessage
{
    /**
     * Constructor.
     *
     * @return void
     */
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
        $this->setKey('Paused', 'true');
    }
}
