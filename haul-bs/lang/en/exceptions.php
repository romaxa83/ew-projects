<?php

return [
    'forbidden' => 'Forbidden',
    'value_object' => [
        'object_must_be_instance_class' => 'The object must be an instance of the class [:class]',
        'value_must_be_email' => 'Value [:value] must be a valid email!',
        'value_must_be_phone' => 'Value [:value] must be a valid phone!'
    ],
    'token' => [
        'not_verified' => 'Token not verified'
    ],
    'user' => [
        'not_found' => 'User not found.',
        'not_pending_status' => 'User is not in Pending status',
        'email' => [
            'verified' => 'The user\'s email has been verified'
        ]
    ],
    'supplier' => [
        'not_found' => 'Supplier not found.',
        'has_inventory' => "Supplier has inventory assigned. Please check the list of inventory. <a href=\":link\">Check inventory list</a>"
    ],
    'customer' => [
        'cant_delete_not_owner' => 'You can\'t delete a customer, you are not attached to it',
        'not_found' => 'Customer not found.',
        'has_truck_and_trailer' => 'This customer has <a href=":trucks">trucks</a> and <a href=":trailers">trailers</a> assigned.',
        'has_truck' => 'This customer has <a href=":trucks">trucks</a> assigned.',
        'has_trailer' => 'This customer has <a href=":trailers">trailers</a> assigned.',
        'address' => [
            'not_found' => 'Customer address not found.',
            'more_limit' => 'Max count of addresses reached.',
            'cant_edit_ecomm_address' => 'This address can\'t be edited.',
            'cant_delete_ecomm_address' => 'This address can\'t be deleted.',
        ]
    ],
    'tag' => [
        'not_found' => 'Tag not found.',
        'more_limit' => 'Max count of tags reached.',
        'used_customer' => 'This tag is already used for <a href=":link">vehicle owners</a>',
        'has_truck_and_trailer' => 'This tag is already used for  <a href=":trucks">trucks</a> and  <a href=":trailers">trailers</a>',
        'has_truck' => 'This tag is already used for  <a href=":trucks">trucks</a>',
        'has_trailer' => 'This tag is already used for  <a href=":trailers">trailers</a>',
    ],
    'localization' => [
        'default_language_not_set' => 'Default language not set.',
        'timezone' => [
            'incorrect_country_code' => 'Incorrect country code.',
            'incorrect' => 'Incorrect timezone.'
        ]
    ],
    'file' => [
        'not_found' => 'File not found.'
    ],
    'comment' => [
        'not_found' => 'Comment not found.'
    ],
    'vehicles' => [
        'truck' => [
            'not_found' => 'Truck not found.',
            'has_open_and_deleted_orders' => 'This truck is used in the <a href=":open_orders">open</a> and <a href=":deleted_orders">deleted</a> orders. Please delete orders permanently first.',
            'has_deleted_orders' => 'This truck is used in the <a href=":deleted_orders">deleted</a> orders. Please delete orders permanently first.',
            'has_open_orders' => 'This truck is used in the <a href=":open_orders">open</a> orders. Please delete orders permanently first.',
        ],
        'trailer' => [
            'not_found' => 'Trailer not found.',
            'has_open_and_deleted_orders' => 'This trailer is used in the <a href=":open_orders">open</a> and <a href=":deleted_orders">deleted</a> orders. Please delete orders permanently first.',
            'has_deleted_orders' => 'This trailer is used in the <a href=":deleted_orders">deleted</a> orders. Please delete orders permanently first.',
            'has_open_orders' => 'This trailer is used in the <a href=":open_orders">open</a> orders. Please delete orders permanently first.',
        ]
    ],
    'type_of_works' => [
        'not_found' => 'Type of work not found.'
    ],
    'inventories' => [
        'inventory' => [
            'not_found' => 'Inventory not found.',
            'cant_deleted_in_stock' => 'This part is not out of stock and can not be deleted.',
        ],
        'transaction' => [
            'not_found' => 'Inventory transaction not found.',
        ],
        'unit' => [
            'not_found' => 'Inventory unit not found.',
            'has_related_entities' => 'This unit of measurement is used for some parts.'
        ],
        'brand' => [
            'not_found' => 'Inventory brand not found.',
            'has_related_entities' => 'This brand is used for some parts.'
        ],
        'category' => [
            'not_found' => 'Inventory category not found.',
            'has_inventories' => 'This category is used for some inventory. Please check the list of inventory with this category. <a href=":link">Check Inventory</a>'
        ],
        'features' => [
            'feature' => [
                'not_found' => 'Inventory feature not found.',
                'has_inventory' => 'This feature is used for some parts.'
            ],
            'value' => [
                'not_found' => 'Inventory feature value not found.',
                'has_inventory' => 'This feature value is used for some parts.'
            ]
        ]
    ],
    'seo' => [
        'not_found' => 'Seo data not found.'
    ],
    'orders' => [
        'bs' => [
            'not_found' => 'Body shop order not found.',
            'finished_order_cant_be_deleted' => 'Finished order can\'t be deleted',
            'finished_order_cant_be_edited' => 'Finished order can\'t be edited',
            'paid_order_cant_be_edited' => 'Order is paid. Please delete the payment first to edit the order.',
            'not_found_payment' => 'Payment body-shop order not found.',

        ],
        'parts' => [
            'not_found' => 'Parts order not found.',
            'not_found_item' => 'Parts order item not found.',
            'cant_delete' => 'The order can\'t be deleted.',
            'cant_delete_paid' => 'The order has been paid and can\'t be deleted',
            'cant_edit' => 'The order can\'t be edited.',
            'cant_switch_status_not_sales' => 'Can\'t switch status, manager is not attached',
            'cant_change_refunded' => 'Can\'t switch to refunded',
            'cant_delete_last_item' => 'Can\'t delete the last item in an order',
            'must_have_delivery_address' => 'Order must have a delivery address.',
            'must_have_billing_address' => 'Order must have a billing address.',
            'must_have_shipping_methods' => 'Order must have a shipping methods.',
            'must_have_items' => 'Order must have a inventory items.',
            'must_have_payment_methods' => 'Order must have a payment methods.',
            'must_have_delivery_type' => 'Order must have a delivery type.',
            'must_be_draft' => 'Order must have a payment methods.',
            'must_not_be_draft' => 'Order must not be in draft state.',
            'not_found_payment' => 'Payment parts shop order not found.',
        ],
        'status_cant_be_change' => 'Can\'t change order status',
        'if_status_change_shipping_method_must_be_pickup' => "When changing the order status, the shipping method must be 'pickup'",
        'must_be_paid' => 'The order must be paid',
        'cant_add_payment' => 'The order can\'t add payment.'
    ]
];

