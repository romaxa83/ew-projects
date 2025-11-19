<?php

return [
    'ip_restriction' => env('TELESCOPE_IP_RESTRICTION', false),

    'allowed_ips' => explode(',', env('TELESCOPE_ALLOWED_IPS', '')),
];
