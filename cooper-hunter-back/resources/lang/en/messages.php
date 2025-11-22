<?php

return [
    'do_not_reply' => 'Please do not reply to this message.',

    'response' => [],

    'regards' => 'Regards',

    'sms' => [
        'auth_code' => 'Your ' . config('app.name') . ' auth code is :code',
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
        'commissioning_start' => 'Commissioning started',
        'is_commissioning_start' => 'This project is start pre-commissioning',
        'set_warranty' => 'Set a warranty for this commercial project'
    ],

    'warranty' => [
        'registered_old' => 'The product is #product#',
        'registered' => 'The status of the product #product# is ":status"',
        'not_registered' => 'Serial number - :serial - not registered',
        'notification' => [
            'subject' => 'Warranty status changed',
            'greeting' => 'Thank you for choosing Cooper&Hunter product. Your warranty status is:',
            'reason' => 'Reason',
            'limited_warranty' => 'LIMITED WARRANTY',
            'warranty_service_disclaimer' => 'FOR WARRANTY SERVICE OR REPAIR CONTACT SERVICE COMPANY NEAR YOU OR YOUR INSTALLING CONTRACTOR',
            'customer_details' => 'Customer Details',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'address' => 'Address',
            'city' => 'City',
            'contractor_name' => 'Contractor Name',
            'license' => 'License',
            'installation_details' => 'Installation Details',
            'purchase_date' => 'Purchase date',
            'purchase_place' => 'Purchase place',
            'installation_date' => 'Installation date',
            'warranty_submitted' => 'Warranty submitted',
            'registered_products' => 'Registered products',
            'model_name' => 'Model name',
            'serial_number' => 'Serial number',
            'faq_note' => 'Note: For any enquiries or concerns please refer to asking question here',
        ],
    ],

    'question' => [
        'subject' => 'Answer to question',
        'greeting' => 'Hello, :Name!',
        'line_1' => 'You got an answer to your question:',
        'line_2' => 'The answer is:',
    ],

    //
    'create_admin' => [
        'greeting' => 'Hello, :name!',
        'subject' => 'Registration',
        'line_1' => 'Your login details:',
        'line_2' => 'Use the password to log into your account.',
        'line_3' => 'After entering your personal account, we strongly recommend that you change the password to a more understandable and reliable one.',
    ],

    'dealer' => [
        'send_credentials' => [
            'greeting' => 'Hello, :name!',
            'subject' => 'Account credentials',
            'line_1' => 'You have been invited to join :company_name dealer account on the Cooper&Hunter web portal. Please use the credentials below to login to the account.',
            'line_2' => 'Your login:',
            'line_3' => 'Your password:',
            'line_4' => 'Thank you for your business!',
        ],
        'order' => [
            'packing_slip' => [
                'update_success' => 'Updated successfully'
            ],
            'checkout' => [
                'success' => 'Order sent for processing',
                'po not specified' => 'PO not specified',
                'not items' => 'The order must contain at least one item',
                'order already sent' => 'This order has already sent'
            ],
            'as_approved' => [
                'subject' => 'Approved order',
            ],
            'manager' => [
                'subject' => 'New order PO #:po',
                'body' => 'The new order has been placed PO #:po by customer :name',
            ],
            'commercial_manager' => [
                'subject' => 'New order PO #:po',
                'body' => 'The new order has been placed PO #:po by customer :name',
            ],
            'file' => [
                'not items for create' => 'There are no products to create an order.'
            ]
        ]
    ],

    'products_count' => ':count Product|:count Products',

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

    'company' => [
        'title' => 'Company',
        'send_code' => [
            'subject' => 'Send a authorization code',
            'greeting' => 'Hello, :Name!',
            'line_1' => 'Thank you for your request to became a dealer. We are happy to inform you that your request has been approved. Please use the credentials below to register your account.',
            'line_2' => 'Your Cooper&Hunter representative:',
            'line_3' => 'Your login: :login',
            'line_4' => 'Authorization code: :code',
            'line_5' => 'Please click the button below to register your account.',
            'line_6' => 'Thank you for your business!',
            'message' => 'Sent code to dealer',
            'has no code' => 'Dealer has no code',
        ],
        'send_data_to_onec' => [
            'message' => 'Company data sent to the onec service',
            'has guid' => 'Company has guid',
        ]
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
        'brand' => 'Brand',
        'qty' => 'Quantity',
        'packing_slip' => 'Packing slip',
        'report' => [
            'company' => 'Company',
            'location' => 'Location',
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
