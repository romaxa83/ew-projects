<?php

return [
    'rules' => [
        'phone' => [
            'pattern' => '/^\+380\d{9}$/',
            'format_message' => '+380XXXXXXXXX',
        ],
        'mask' => [
            'pattern' => '/^\+38\s\(0\d{2}\)\s\d{3}\s\d{2}\s\d{2}$/',
            'format_message' => '+38 (0XX) XXX XX XX',
        ],
        'phone_or_mask' => [
            'pattern' => '/^(\+38\s\(0\d{2}\)\s\d{3}\s\d{2}\s\d{2})|(\+380\d{9})$/',
            'format_message' => '+38 (0XX) XXX XX XX :or +380XXXXXXXXX',
        ],
    ],
    'mask' => '+38 (099) 999 99 99', // For js plugin initialization
    'mask_format' => '+38 (0XX) XXX XX XX', // "X" will be replaced with real digit
];
