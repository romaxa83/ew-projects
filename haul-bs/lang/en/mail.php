<?php

return [
    'forgot_password' => [
        'greeting' => 'Hello, :name!',
        'subject' => 'Password reset',
        'line_1' => 'There was a request to change your password!',
        'line_2' => 'If you did not make this request then please ignore this email.',
        'line_3' => 'Otherwise, please click this link to change your password: <a href=":link" class="content-link">Link</a>',
    ],

    'reset_password' => [
        'greeting' => 'Hello, :name!',
        'subject' => 'New password',
        'line_1' => 'Your new password: <strong>:password</strong>',
        'line_2' => 'Use the specified password to log into your account.',
        'line_3' => 'After entering your personal account, we strongly recommend that you change the password to a more understandable and reliable one.',
    ],

    'email_verification' => [
        'greeting' => 'Hello, :name!',
        'subject' => 'Email verification',
        'line_1' => 'Follow the <a href=":link" class="content-link">link</a> to verify your email: <a href=":link" class="content-link">Link</a>',
    ],

    'send_credential' => [
        'greeting' => 'Hello, :name!',
        'subject' => 'Register ' . remove_underscore(config('app.name')),
        'body' => 'You have successfully registered in your personal account',
        'login' => 'Login: :login',
        'password' => 'Password: :password',
    ],
    'customer' => [
        'register_in_haulk_depot' => [
            'subject' => 'Register on the Haulk Depot website',
            'greeting' => 'Hello, :name!',
            'body' => 'You are welcome to create your account on the Haulk Depot website. Do not wait for the Sales manager to place your order. Have the capability to browse all the available products, make your purchase at any time, check the delivery status of your orders in your account and many other perks!',
        ]
    ]
];

