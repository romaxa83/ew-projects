<?php

use App\Channels\FaxChannel;
use Illuminate\Foundation\Application;

Notification::extend(
    'fax',
    function (Application $application) {
        return $application->make(FaxChannel::class);
    }
);
