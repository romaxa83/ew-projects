<?php

namespace App\PAMI\Message\Action;

/**
 * Ping action message.
 */
class PingAction extends ActionMessage
{
    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Ping');
    }
}
