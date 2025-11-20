<?php

namespace App\PAMI\Message\Action;

/**
 * Request to withdraw a caller from the queue back to the dialplan.
 * @see https://wiki.asterisk.org/wiki/display/AST/Asterisk+16+ManagerAction_QueueWithdrawCaller
 */
class QueueWithdrawCallerAction extends ActionMessage
{
    public function __construct($withdrawInfo, $queue = false, $caller = false)
    {
        parent::__construct('QueueWithdrawCaller');
        if ($queue !== false) {
            $this->setKey('Queue', $queue);
        }
        if ($caller !== false) {
            $this->setKey('Caller', $caller);
        }
        $this->setKey('WithdrawInfo', $withdrawInfo);
    }
}

