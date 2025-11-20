<?php

namespace App\PAMI\Message\Action;

/**
 * SendText action message.
 */
class SendTextAction extends ActionMessage
{
    public function __construct($channel, $message)
    {
        parent::__construct('SendText');
        $this->setKey('Channel', $channel);
        $this->setKey('Message', $message);
    }
}
