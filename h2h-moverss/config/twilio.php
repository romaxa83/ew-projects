<?php

return [
    'twilio' => [
        'default' => 'twilio',
        'messaging_sid' => env('TWILIO_MG_SERVICE', ''),
        'connections' => [
            'twilio' => [
                /*
                |--------------------------------------------------------------------------
                | SID
                |--------------------------------------------------------------------------
                |
                | Your Twilio Account SID #
                |
                */
                'sid' => env('TWILIO_SID', ''),

                /*
                |--------------------------------------------------------------------------
                | Access Token
                |--------------------------------------------------------------------------
                |
                | Access token that can be found in your Twilio dashboard
                |
                */
                'token' => env('TWILIO_TOKEN', ''),

                /*
                |--------------------------------------------------------------------------
                | From Number
                |--------------------------------------------------------------------------
                |
                | The Phone number registered with Twilio that your SMS & Calls will come from
                |
                */
                'from' => env('TWILIO_FROM', ''),
            ],
            'california' => [
                'sid' => env('TWILIO_SID', ''),
                'token' => env('TWILIO_TOKEN', ''),
                'from' => env('TWILIO_LA_SMS_CALLER_ID', ''),
            ],
            'illinois' => [
                'sid' => env('TWILIO_SID', ''),
                'token' => env('TWILIO_TOKEN', ''),
                'from' => env('TWILIO_IL_SMS_CALLER_ID', ''),
            ],
        ],
    ],
];
