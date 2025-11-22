<?php

$frontUrl = trim(env('FRONTEND_URL', 'http://localhost'), '/');

$emailConfirmUri = trim(env('FRONTEND_URL_EMAIL_CONFIRM', 'email-confirm'), '/');
$forgotPasswordUri = trim(env('FRONTEND_URL_PASS_FORGOT', 'reset-password'), '/');

return [
    'home' => $frontUrl,
    'thank-you-page' => $frontUrl . '/login',
    'email-confirmation' => $frontUrl . '/' . $emailConfirmUri,
    'password-forgot' => $frontUrl . '/' . $forgotPasswordUri,
    'not-found' => $frontUrl . '/404',
    'faq' => $frontUrl . '/faq',
    'product' => $frontUrl . '/product/',
    'account-commercial' => $frontUrl . '/account/commercial',
];
