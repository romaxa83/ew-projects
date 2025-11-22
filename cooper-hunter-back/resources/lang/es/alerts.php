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
            'title' => 'Orden crear',
            'description' => 'El técnico ha creado un nuevo pedido (correo electrónico: :email).',
        ],
        AlertOrderEnum::CHANGE_STATUS => [
            'title' => 'Estado del pedido',
            'description' => 'El estado de su orden a sido cambiado a :status.'
        ]
    ],
    AlertModelEnum::SUPPORT_REQUEST => [
        AlertSupportRequestEnum::NEW_REQUEST => [
            'title' => 'Solicitud',
            'description' => 'Se recibió una nueva solicitud.'
        ],
        AlertSupportRequestEnum::NEW_MESSAGE => [
            'title' => 'Solicitud',
            'description' => 'A recibido un nuevo mensaje.'
        ],
        AlertSupportRequestEnum::CLOSE => [
            'title' => 'Solicitar cerrar',
            'description' => 'Su solicitud a sido cerrada.'
        ]
    ],
    AlertModelEnum::TECHNICIAN => [
        AlertTechnicianEnum::MODERATION_READY => [
            'title' => 'Moderación',
            'description' => 'Su cuenta ha sido moderada.'
        ],
        AlertTechnicianEnum::RE_MODERATION => [
            'title' => 'Moderación',
            'description' => 'Su cuenta ha sido enviada para re-moderación.'
        ],
        AlertTechnicianEnum::NEW_RE_MODERATION => [
            'title' => 'Moderación',
            'description' => 'Cuenta de nuevo técnico para re-moderación. Correo electrónico del técnico: :email'
        ],
        AlertTechnicianEnum::EMAIL_VERIFICATION_PROCESS => [
            'title' => 'Verificacion de email',
            'description' => 'Verificacion del correo electronico requirido.'
        ],
        AlertTechnicianEnum::EMAIL_VERIFICATION_READY => [
            'title' => 'Verificacion de email',
            'description' => 'Su correo electronico a sido verificado.',
        ],
        AlertTechnicianEnum::REGISTRATION => [
            'title' => 'Registro',
            'description' => 'Nuevo miembro técnico registrado. Correo electrónico: :email',
        ],
    ],
    AlertModelEnum::USER => [
        AlertUserEnum::REGISTRATION => [
            'title' => 'Registro',
            'description' => 'Nuevo usuario miembro registrado. Correo electrónico: :email',
        ]
    ],
    AlertModelEnum::DEALER => [
        AlertDealerEnum::EMAIL_VERIFICATION_PROCESS => [
            'title' => 'Verificacion de email',
            'description' => 'Verificacion del correo electronico requirido.'
        ],
        AlertDealerEnum::EMAIL_VERIFICATION_READY => [
            'title' => 'Verificacion de email',
            'description' => 'Su correo electronico a sido verificado.',
        ],
        AlertDealerEnum::REGISTRATION => [
            'title' => 'Registro',
            'description' => 'Nuevo miembro distribuidor registrado. Correo electrónico: :email',
        ]
    ],
    AlertModelEnum::SYSTEM => [
        AlertSystemEnum::WARRANTY_STATUS => [
            'title' => 'Warranty registration',
            'description' => 'El estado de su registracion de la garantia a sido cambiado a :status',
        ]
    ],
];
