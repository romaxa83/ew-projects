<?php

namespace WezomCms\Firebase\Services\Sender;

use WezomCms\Firebase\Messages\FcmPayload;

interface FirebaseSender
{
    public function __construct(string $url, string $serverKey);

    public function send(FcmPayload $data);
}
