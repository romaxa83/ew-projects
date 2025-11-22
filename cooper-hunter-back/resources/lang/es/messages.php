<?php

return [
    'do_not_reply' => 'Please do not reply to this message.',

    'response' => [],

    'regards' => 'Respetuosamente',

    'sms' => [
        'auth_code' => 'Your ' . config('app.name') . ' auth code is :code',
    ],

    'company' => [
        'title' => 'Company',
        'send_code' => [
            'subject' => 'Enviar un código de autorización',
            'greeting' => 'Hola, :Name!',
            'line_1' => 'Gracias por su solicitud para convertirse en distribuidor. Nos complace informarle que su solicitud ha sido aprobada. Utilice las credenciales a continuación para registrar su cuenta.',
            'line_2' => 'Su representante de Cooper&Hunter:',
            'line_3' => 'Su inicio de sesión: :login',
            'line_4' => 'Código de autorización: :code',
            'line_5' => 'Por favor, haga clic en el botón de abajo para registrar su cuenta.',
            'line_6' => 'Gracias por hacer negocios!',
            'message' => 'Sent code to dealer',
            'has no code' => 'Dealer has no code',
        ],
    ],

    'dealer' => [
        'send_credentials' => [
            'greeting' => 'Hola, :name!',
            'subject' => 'Credenciales de cuenta',
            'line_1' => 'Ha sido invitado a unirse a la cuenta de distribuidor de :company_name en el portal web de Cooper&Hunter. Utilice las credenciales a continuación para iniciar sesión en la cuenta.',
            'line_2' => 'Su nombre de usuario:',
            'line_3' => 'Tu contraseña:',
            'line_4' => 'Gracias por hacer negocios!',
        ]
    ],

    'commercial' => [
        'rdp' => [
            'greeting' => 'Hello, :Name!',
            'subject' => 'RDP Credentials',
            'disclaimer' => 'This email content your personal credentials. Please do not disclose your credential.',
            'line_1' => 'Hello :Name, your request regarding access to the Cooper&Hunter selection software has been approved.',
            'line_2' => 'Please login to your account on <a href=":link" class="content-link">cooperandhunter.us</a> to get more information for connecting to the App.',
        ],
        'quote' => [
            'subject' => 'Commercial quote',
            'greeting' => 'Hello, :name!',
            'body' => 'Thank you for reaching out to us about your commercial project: :project_name. According to your request we’ve prepared an estimate for you. Please review it below',
            'email_send' => 'Estimated email sent',
        ],
    ],

    'warranty' => [
        'registered_old' => 'El producto es #product#',
        'registered' => 'Este producto #product# - está ":status"',
        'not_registered' => 'Número de serie - :serial - no registrado',
        'notification' => [
            'subject' => 'El estado de la garantía cambió',
            'greeting' => 'Gracias por elegir el producto Cooper&Hunter. El estado de su garantía es:',
            'reason' => 'Razón',
            'limited_warranty' => 'GARANTÍA LIMITADA',
            'warranty_service_disclaimer' => 'PARA EL SERVICIO DE GARANTÍA O LA REPARACIÓN CONTACTE A LA EMPRESA DE SERVICIO CERCANA A USTED O A SU CONTRATISTA DE INSTALACIÓN',
            'customer_details' => 'Detalles del cliente',
            'first_name' => 'Primer nombre',
            'last_name' => 'Apellido',
            'email' => 'Correo electrónico',
            'phone' => 'Número de teléfono',
            'address' => 'Dirección',
            'city' => 'Ciudad',
            'contractor_name' => 'El nombre del contratista',
            'license' => 'Licencia',
            'installation_details' => 'Detalles de instalación',
            'purchase_date' => 'Fecha de compra',
            'purchase_place' => 'Lugar de compra',
            'installation_date' => 'Fecha de instalación',
            'warranty_submitted' => 'Garantía enviada',
            'registered_products' => 'Productos registrados',
            'model_name' => 'Nombre del modelo',
            'serial_number' => 'Número de serie',
            'faq_note' => 'Nota: Para cualquier consulta o inquietud, consulte la sección de preguntas aquí',
        ],
    ],

    'question' => [
        'subject' => 'Respuesta a la pregunta',
        'greeting' => 'Hola, :Name!',
        'line_1' => 'Tienes una respuesta a tu pregunta:',
        'line_2' => 'La respuesta es:',
    ],

    'products_count' => ':count Producto|:count Productos',

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

    'email_confirmation' => [
        'greeting' => 'Hello, :name!',
        'subject' => 'Email confirmation',
        'line_1' => sprintf('Thanks for signing up for %s.', config('app.name')),
        'line_2' => 'Please click the button below to verify your email address.',
        'button' => 'Verify',
    ],

    'roles' => [
        'set-as-default-for-owner' => 'The role is set as default.',
        'cant-be-toggled' => 'Cant be toggled. Set for other role.',
    ],

    'user' => [
        'email-is-not-verified' => 'Your email is not verified.',
    ],

    'admin' => [
        'title' => 'Admin',
        'actions' => [
            'delete' => [
                'fail' => [
                    'reasons' => [
                        'by-myself' => 'You can`t delete yourself.'
                    ],
                ],
                'success' => [
                    'one-entity' => 'Deleting admin was successful.',
                    'many-entity' => 'Deleting admins was successful.',
                ],
            ],
        ],
    ],
    'catalog' => [
        'category' => [
            'actions' => [
                'delete' => [
                    'success' => [
                        'one-entity' => 'Deleting category was successful.',
                        'many-entity' => 'Deleting categories was successful.',
                    ]
                ]
            ],
        ],
        'product' => [
            'actions' => [
                'delete' => [
                    'success' => [
                        'one-entity' => 'Deleting unit was successful.',
                        'many-entity' => 'Deleting units was successful.',
                    ]
                ]
            ],
        ],
        'feature' => [
            'value' => [
                'actions' => [
                    'delete' => [
                        'success' => [
                            'one-entity' => 'Deleting value was successful.',
                            'many-entity' => 'Deleting values was successful.',
                        ]
                    ]
                ],
            ],
            'feature' => [
                'actions' => [
                    'delete' => [
                        'success' => [
                            'one-entity' => 'Deleting feature was successful.',
                            'many-entity' => 'Deleting features was successful.',
                        ]
                    ]
                ],
            ],
        ],
        'video' => [
            'group' => [
                'actions' => [
                    'delete' => [
                        'success' => [
                            'one-entity' => 'Deleting group was successful.',
                            'many-entity' => 'Deleting groups was successful.',
                        ]
                    ]
                ],
            ],
            'link' => [
                'actions' => [
                    'delete' => [
                        'success' => [
                            'one-entity' => 'Deleting link was successful.',
                            'many-entity' => 'Deleting links was successful.',
                        ]
                    ]
                ],
            ],
        ],
        'certificate' => [
            'certificate' => [
                'actions' => [
                    'delete' => [
                        'success' => [
                            'one-entity' => 'Deleting certificate was successful.',
                            'many-entity' => 'Deleting certificates was successful.',
                        ]
                    ]
                ],
            ],
            'type' => [
                'actions' => [
                    'delete' => [
                        'success' => [
                            'one-entity' => 'Deleting type of certificate was successful.',
                            'many-entity' => 'Deleting types of certificate was successful.',
                        ]
                    ]
                ],
            ],
        ],
        'troubleshoots' => [
            'group' => [
                'actions' => [
                    'delete' => [
                        'success' => [
                            'one-entity' => 'Deleting group troubleshoot was successful.',
                            'many-entity' => 'Deleting groups troubleshoot was successful.',
                        ]
                    ]
                ],
            ],
            'troubleshoot' => [
                'actions' => [
                    'delete' => [
                        'success' => [
                            'one-entity' => 'Deleting troubleshoot was successful.',
                            'many-entity' => 'Deleting troubleshoot was successful.',
                        ]
                    ]
                ],
            ],
        ],

        'solutions' => [
            'notification' => [
                'greeting' => 'Hello,',
                'subject' => 'Find solution PDF file',
                'line' => 'The PDF file in attachments.'
            ]
        ]
    ],
    'file' => [
        'id' => 'ID',
        'serial_number' => 'Serial number',
        'name' => 'Name',
        'report' => [
            'date' => 'Date/Company',
            'po' => 'P.O.',
            'product' => 'Product/Service',
            'desc' => 'Memo/Description',
            'qty' => 'Qty',
            'price' => 'Seles Price',
            'amount' => 'Amount',
        ]
    ]
];
