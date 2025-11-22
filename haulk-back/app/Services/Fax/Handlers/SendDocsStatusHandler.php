<?php

namespace App\Services\Fax\Handlers;

use App\Events\ModelChanged;

class SendDocsStatusHandler extends SendOrderStatusHandler
{

    public function afterFail()
    {
        event(
            new ModelChanged(
                $this->getOrder(),
                $this->getFailMessage(),
                [
                    'load_id' => $this->getOrderLoadId(),
                    'number' => $this->getMessageTo(),
                ]
            )
        );
    }

    protected function getFailMessage(): string
    {
        if ($this->getFileName() === 'bol.pdf') {
            return 'history.send_bol_via_fax_failed';
        }
        if ($this->getFileName() === 'w9.pdf') {
            return 'history.send_w9_via_fax_failed';
        }

        return 'history.send_invoice_via_fax_failed';
    }

    public function afterSuccess()
    {
        event(
            new ModelChanged(
                $this->getOrder(),
                $this->getSuccessMessage(),
                [
                    'load_id' => $this->getOrderLoadId(),
                    'number' => $this->getMessageTo(),
                ]
            )
        );
    }

    protected function getSuccessMessage(): string
    {
        if ($this->getFileName() === 'bol.pdf') {
            return 'history.send_bol_via_fax_success';
        }
        if ($this->getFileName() === 'w9.pdf') {
            return 'history.send_w9_via_fax_success';
        }

        return 'history.send_invoice_via_fax_success';
    }

}
