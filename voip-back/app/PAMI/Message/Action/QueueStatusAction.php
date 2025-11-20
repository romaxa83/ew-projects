<?php

namespace App\PAMI\Message\Action;

/**
 * QueueStatus action message.
 */
class QueueStatusAction extends ActionMessage
{
    public function __construct($queue = false, $member = false)
    {
        parent::__construct('QueueStatus');
        if ($queue != false) {
            $this->setKey('Queue', $queue);
        }
        if ($member != false) {
            $this->setKey('Member', $member);
        }
    }
}
