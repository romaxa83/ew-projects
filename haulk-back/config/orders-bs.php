<?php

return [
    'do_not_change_finished_status_after' => 1440, //time in minutes
    'purge_after' => env('BS_ORDERS_PURGE_AFTER', 30),//days
    'delete_after' => env('BS_ORDERS_DELETE_AFTER', 730),//days
];
