<?php

namespace App\Services\Fax\Handlers;

use App\Events\ModelChanged;

class SendBolStatusHandler extends SendOrderStatusHandler
{

    public function afterFail()
    {
        event(
            new ModelChanged(
                $this->getOrder(),
                'history.send_bol_via_fax_failed',
                [
                    'load_id' => $this->getOrderLoadId(),
                    'number' => $this->getMessageTo(),
                ]
            )
        );
    }

    public function afterSuccess()
    {
        event(
            new ModelChanged(
                $this->getOrder(),
                'history.send_bol_via_fax_success',
                [
                    'load_id' => $this->getOrderLoadId(),
                    'number' => $this->getMessageTo(),
                ]
            )
        );
    }

}
