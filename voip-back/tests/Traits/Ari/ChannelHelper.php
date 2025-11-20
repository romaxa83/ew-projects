<?php

namespace Tests\Traits\Ari;

use OpiyOrg\AriClient\Model\CallerID;
use OpiyOrg\AriClient\Model\Channel;

trait ChannelHelper
{
    public function createCallerID(
        string $name = 'test',
        string $number = '390',
    ): CallerID
    {
        $model = new CallerID();
        $model->name = $name;
        $model->number = $number;

        return $model;
    }

    public function createChannel(
        CallerID $caller,
        CallerID $connected,
        string $name = 'PJSIP/kamailio-000011a6',
        string $createTime = '2023-05-09T13:47:27.905+0300',
        string $state = 'Ring',
        string $accountcode = '',
        string $id = 'asterisk-docker01-1683639359.48398',
    ): Channel
    {
        $model = new Channel();
        $model->caller = $caller;
        $model->connected = $connected;
        $model->name = $name;
        $model->creationtime = $createTime;
        $model->state = $state;
        $model->accountcode = $accountcode;
        $model->id = $id;

        return $model;
    }
}
