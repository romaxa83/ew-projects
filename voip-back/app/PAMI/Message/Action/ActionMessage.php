<?php

namespace App\PAMI\Message\Action;

use App\PAMI\Message\OutgoingMessage;
use App\PAMI\Exception\PAMIException;

/**
 * A generic action ami message.
 */
abstract class ActionMessage extends OutgoingMessage
{
    /**
     * Constructor.
     *
     * @param string $what Action command.
     *
     * @return void
     */
    public function __construct($what)
    {
        parent::__construct();
        $this->setKey('Action', $what);
        $this->setKey('ActionID', microtime(true));
    }

    /**
     * Sets Action ID.
     *
     * The ActionID can be at most 69 characters long, according to
     * {@link https://issues.asterisk.org/jira/browse/14847 Asterisk Issue 14847}.
     *
     * Therefore we'll throw an exception when the ActionID is too long.
     *
     * @param $actionID The Action ID to have this action known by
     *
     * @return void
     * @throws PAMIException When the ActionID is more then 69 characters long
     */
    public function setActionID($actionID)
    {
        if (0 == strlen($actionID)) {
            throw new PAMIException('ActionID cannot be empty.');
        }

        if (strlen($actionID) > 69) {
            throw new PAMIException('ActionID can be at most 69 characters long.');
        }

        $this->setKey('ActionID', $actionID);
    }
}
