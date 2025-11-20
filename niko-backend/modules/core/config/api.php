<?php

return [
    'enabled' => env('API_ENABLED', false),
    'version' => 1,
    'default_limit' => 30,
    'default_offset' => 0,
    'limit_as' => 'size',
    'offset_as' => 'page',
    'search_as' => 'query',
];
