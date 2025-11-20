<?php

namespace App\PAMI\Message\Action;

/**
 * Command action message.
 */
class CommandAction extends ActionMessage
{
    public function __construct($command)
    {
        parent::__construct('Command');
        $this->setKey('Command', $command);
    }
}
