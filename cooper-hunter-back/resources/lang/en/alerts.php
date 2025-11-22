<?php

use App\Enums\Alerts\AlertDealerEnum;
use App\Enums\Alerts\AlertModelEnum;
use App\Enums\Alerts\AlertOrderEnum;
use App\Enums\Alerts\AlertSupportRequestEnum;
use App\Enums\Alerts\AlertSystemEnum;
use App\Enums\Alerts\AlertTechnicianEnum;
use App\Enums\Alerts\AlertUserEnum;

return [
    AlertModelEnum::ORDER => [
        AlertOrderEnum::CREATE => [
            'title' => 'Order create',
            'description' => 'New order has been created by technician (email: :email).',
        ],
        AlertOrderEnum::CHANGE_STATUS => [
            'title' => 'Order status',
            'description' => 'The status of your order has been changed to :status.'
        ]
    ],
    AlertModelEnum::SUPPORT_REQUEST => [
        AlertSupportRequestEnum::NEW_REQUEST => [
            'title' => 'Request',
            'description' => 'A new request was received.'
        ],
        AlertSupportRequestEnum::NEW_MESSAGE => [
            'title' => 'Request',
            'description' => 'A new message was received.'
        ],
        AlertSupportRequestEnum::CLOSE => [
            'title' => 'Request close',
            'description' => 'Your request has been closed.'
        ]
    ],
    AlertModelEnum::TECHNICIAN => [
        AlertTechnicianEnum::MODERATION_READY => [
            'title' => 'Account moderation',
            'description' => 'Your account has been updated.'
        ],
        AlertTechnicianEnum::RE_MODERATION => [
            'title' => 'Account moderation',
            'description' => 'Your account has been sent for re-moderation.'
        ],
        AlertTechnicianEnum::NEW_RE_MODERATION => [
            'title' => 'Account moderation',
            'description' => 'New technician\'s account for re-moderation. Technician email: :email'
        ],
        AlertTechnicianEnum::EMAIL_VERIFICATION_PROCESS => [
            'title' => 'Account moderation',
            'description' => 'Email verification required.'
        ],
        AlertTechnicianEnum::EMAIL_VERIFICATION_READY => [
            'title' => 'Account moderation',
            'description' => 'Your email has been verified successfully!',
        ],
        AlertTechnicianEnum::REGISTRATION => [
            'title' => 'Registration',
            'description' => 'New technician member registered. Email: :email',
        ]
    ],
    AlertModelEnum::USER => [
        AlertUserEnum::REGISTRATION => [
            'title' => 'Registration',
            'description' => 'New user member registered. Email: :email',
        ]
    ],
    AlertModelEnum::DEALER => [
        AlertDealerEnum::EMAIL_VERIFICATION_PROCESS => [
            'title' => 'Account moderation',
            'description' => 'Email verification required.'
        ],
        AlertDealerEnum::EMAIL_VERIFICATION_READY => [
            'title' => 'Account moderation',
            'description' => 'Your email has been verified successfully!',
        ],
        AlertDealerEnum::REGISTRATION => [
            'title' => 'Registration',
            'description' => 'New dealer member registered. Email: :email',
        ]
    ],
    AlertModelEnum::SYSTEM => [
        AlertSystemEnum::WARRANTY_STATUS => [
            'title' => 'Warranty registration',
            'description' => 'The status of your warranty registration was changed to :status',
        ]
    ],
];
