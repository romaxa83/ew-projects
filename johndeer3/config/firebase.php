<?php

return [
    'enable_firebase' => env('ENABLE_FIREBASE', false),
    'firebase_server_key' => env('FIREBASE_SERVER_KEY', false),
    'firebase_sender_id' => env('FIREBASE_SENDER_ID', false),
    'fcm_send_url' => env('FCM_SEND_URL', 'https://fcm.googleapis.com/fcm/send'),
];

