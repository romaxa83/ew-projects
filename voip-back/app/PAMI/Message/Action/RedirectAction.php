<?php

namespace App\PAMI\Message\Action;

class RedirectAction extends ActionMessage
{
    /**
     * Constructor.
     *
     * @param string $channel   Channel to redirect.
     * @param string $extension Extension to transfer to.
     * @param string $context   Context to transfer to.
     * @param string $priority  Priority to transfer to.
     *
     * @return void
     */
    public function __construct(
        string $channel,
        string $extension,
        string $context,
        string $priority
    )
    {
        parent::__construct('Redirect');
        $this->setKey('Channel', $channel);
        $this->setKey('Exten', $extension);
        $this->setKey('Context', $context);
        $this->setKey('Priority', $priority);
    }

    public function setExtraChannel($channel): void
    {
        $this->setKey('ExtraChannel', $channel);
    }

    public function setExtraExtension($extension): void
    {
        $this->setKey('ExtraExten', $extension);
    }

    public function setExtraContext($context): void
    {
        $this->setKey('ExtraContext', $context);
    }


    public function setExtraPriority($priority): void
    {
        $this->setKey('ExtraPriority', $priority);
    }
}
