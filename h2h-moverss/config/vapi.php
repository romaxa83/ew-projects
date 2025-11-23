<?php

return [
    'excludes_sip' => explode(',', env('VAPI_EXCLUDES_SIP') ?? '')
];
