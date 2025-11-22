<?php

$list = 'List';
$create = 'Create';
$update = 'Update';
$delete = 'Delete';
$deleteSoft = 'Soft delete';
$restore = 'Restore';
$listArchive = 'Archive list';
$add = 'Add';

$baseGrants = [
    'list' => $list,
    'create' => $create,
    'update' => $update,
    'delete' => $delete
];

return [
    'chat' => [
        'group' => 'Chat',
        'grants' => [
            'list' => $list,
            'messaging' => 'Messaging',
        ],
        'menu' => [
            'group' => 'Chat menu',
            'grants' => $baseGrants
        ]
    ],
    'commercial_project' => [
        'group' => 'Commercial Projects',
        'grants' => $baseGrants,
        'start_commissioning' => "Start commissioning",
        'set_warranty' => "Set a warranty"
    ],
    'commercial_quote' => [
        'group' => 'Commercial Quotes',
        'grants' => $baseGrants,
    ],
    'commercial_settings' => [
        'group' => 'Commercial Settings',
        'grants' => $baseGrants,
    ],
    'commercial_credentials' => [
        'group' => 'RDP Credentials',
        'grants' => $baseGrants,
    ],
    'member' => [
        'group' => 'Members',
        'verify_email' => 'Verify email',
    ],
    'commissioning' => [
        'protocol' => [
            'group' => 'Commissioning protocol',
            'grants' => $baseGrants,
        ],
        'question' => [
            'group' => 'Protocol question',
            'grants' => $baseGrants,
        ],
        'answer' => [
            'group' => 'Answer by protocol questions',
            'grants' => $baseGrants,
        ]
    ],
    'about_company' => [
        'group' => 'About company',
        'grants' => $baseGrants,
    ],
    'for_member_page' => [
        'group' => 'For Member Pages',
        'grants' => $baseGrants,
    ],
    'page' => [
        'group' => 'Page',
        'grants' => [
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
        ]
    ],
    'admin' => [
        'group' => 'Admins',
        'grants' => $baseGrants,
    ],
    'user' => [
        'group' => 'Users',
        'grants' => $baseGrants + [
                'delete-soft' => $deleteSoft,
                'restore' => $restore,
                'list-archive' => $listArchive,
            ],
    ],
    'dealer' => [
        'group' => 'Dealers',
        'grants' => $baseGrants + [
                'delete-soft' => $deleteSoft,
                'restore' => $restore,
                'list-archive' => $listArchive,
            ],
    ],
    'company' => [
        'group' => 'Companies',
        'grants' => $baseGrants + [
                'send_code' => 'Send a code'
            ],
        'shipping_address' => [
            'group' => 'Company shipping address',
            'grants' => $baseGrants
        ]
    ],
    'payment' => [
        'group' => 'Payments',
        'grants' => [
            'card' => [
                'add' => $add . ' card',
                'delete' => $delete . ' card',
            ],
        ],
    ],
    'role' => [
        'group' => 'Roles',
        'grants' => $baseGrants,
    ],
    'technician' => [
        'group' => 'Technicians',
        'grants' => $baseGrants + [
                'toggle_status' => 'Toggle status',
                'delete-soft' => $deleteSoft,
                'restore' => $restore,
                'list-archive' => $listArchive,
                'verify-marker' => 'is verified marker',
            ],
    ],
    'fcm' => [
        'group' => 'Fcm tokens',
        'grants' => [
            'add' => 'Add'
        ]
    ],
    'ip-access' => [
        'group' => 'Ip Access',
        'grants' => $baseGrants,
    ],
    'translate' => [
        'group' => 'Translations',
        'grants' => $baseGrants,
    ],
    'catalog' => [
        'pdf' => [
            'group' => 'Pdf',
            'grants' => [
                'upload' => 'Upload pdf',
                'delete' => 'Delete pdf',
            ]
        ],
        'category' => [
            'group' => 'Category',
            'grants' => $baseGrants + [
                    'image_upload' => 'Upload image',
                ],
        ],
        'label' => [
            'group' => 'Label',
            'grants' => $baseGrants,
        ],
        'product' => [
            'group' => 'Product',
            'grants' => $baseGrants + [
                    'image_upload' => 'Product image',
                ],
        ],
        'ticket' => [
            'group' => 'Ticket',
            'grants' => $baseGrants,
        ],
        'feature' => [
            'value' => [
                'group' => 'Feature value',
                'grants' => $baseGrants,
            ],
            'feature' => [
                'group' => 'Feature',
                'grants' => $baseGrants,
            ],
            'specifications' => [
                'group' => 'Innovative Features',
                'grants' => $baseGrants,
            ],
        ],
        'video' => [
            'group' => [
                'group' => 'Group of links',
                'grants' => $baseGrants,
            ],
            'link' => [
                'group' => 'Link',
                'grants' => $baseGrants,
            ],
        ],
        'certificate' => [
            'type' => [
                'group' => 'Type of certificate',
                'grants' => $baseGrants,
            ],
            'certificate' => [
                'group' => 'Certificate',
                'grants' => $baseGrants,
            ],
        ],
        'troubleshoot' => [
            'group' => [
                'group' => 'Group of troubleshoot',
                'grants' => $baseGrants,
            ],
            'troubleshoot' => [
                'group' => 'Troubleshoot',
                'grants' => $baseGrants,
            ],
        ],
        'manual' => [
            'group' => 'Manual',
            'grants' => $baseGrants + [
                    'media_upload' => 'Upload media',
                ],
        ],
        'solution' => [
            'group' => 'Solution',
            'grants' => [
                'create_update' => 'Create/Update',
                'delete' => 'Delete',
                'read' => 'Read',
            ]
        ]
    ],
    'content' => [
        'our_case_category' => [
            'group' => 'Our Case Categories',
            'grants' => $baseGrants,
        ],
        'our_case' => [
            'group' => 'Our Cases',
            'grants' => $baseGrants,
        ],
    ],
    'faq' => [
        'group' => 'Faq',
        'grants' => $baseGrants,
    ],
    'question' => [
        'group' => 'Questions',
        'grants' => $baseGrants + [
                'answer' => 'Answer',
            ],
    ],
    'news' => [
        'group' => 'News',
        'grants' => $baseGrants,
    ],
    'store_category' => [
        'group' => 'Online store categories',
        'grants' => $baseGrants,
    ],
    'store' => [
        'group' => 'Online stores',
        'grants' => $baseGrants,
    ],
    'distributor' => [
        'group' => 'Distributors',
        'grants' => $baseGrants,
    ],
    'menu' => [
        'group' => 'Menu',
        'grants' => $baseGrants,
    ],
    'videos' => [
        'group' => 'Videos',
        'grants' => $baseGrants,
    ],
    'project' => [
        'group' => 'Projects',
        'grants' => $baseGrants,
    ],
    'slider' => [
        'group' => 'Sliders',
        'grants' => $baseGrants,
    ],
    'support' => [
        'group' => 'Support Center',
        'grants' => $baseGrants,
    ],
    'order' => [
        'dealer' => [
            'group' => 'Dealer order',
            'grants' => $baseGrants,
        ],
        'group' => 'Order',
        'grants' => array_merge(
            $baseGrants,
            [
                'paid' => 'Paid'
            ]
        ),
        'category' => [
            'group' => 'Order category',
            'grants' => $baseGrants
        ],

        'delivery_type' => [
            'group' => 'Delivery type',
            'grants' => $baseGrants
        ],
    ],
    'manage_media' => [
        'group' => 'Manage Media',
        'grants' => [
            'manage' => 'Manage'
        ]
    ],
    'app_version' => [
        'group' => 'App Versions',
        'grants' => [
            'manage' => 'Manage'
        ]
    ],
    'support_request' => [
        'group' => 'Support request',
        'grants' => [
            'create' => 'Create',
            'list' => 'List',
            'answer' => 'Answer',
            'close' => 'Close',
        ],
        'subject' => [
            'group' => 'Support request subject',
            'grants' => $baseGrants
        ],
    ],
    'alert' => [
        'group' => 'Alert',
        'grants' => [
            'list' => 'List',
            'set_read' => 'Set read',
            'send' => 'Send',
        ]
    ],
    'warranty_info' => [
        'group' => 'Warranty Info',
        'grants' => $baseGrants
    ],
    'warranty_registration' => [
        'group' => 'Warranty Registrations',
        'grants' => $baseGrants
    ],

    'global_setting' => [
        'group' => 'Global Setting',
        'grants' => $baseGrants
    ],
];
