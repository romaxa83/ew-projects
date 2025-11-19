<?php

return [
    'ip_restriction' => env('HORIZON_IP_RESTRICTION', false),

    'allowed_ips' => explode(',', env('HORIZON_ALLOWED_IPS', '')),
];
