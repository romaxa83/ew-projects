<?php

return [

    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute may only contain letters.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute may only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file' => 'The :attribute must be greater than or equal :value kilobytes.',
        'string' => 'The :attribute must be greater than or equal :value characters.',
        'array' => 'The :attribute must have :value items or more.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file' => 'The :attribute must be less than or equal :value kilobytes.',
        'string' => 'The :attribute must be less than or equal :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
        'string' => 'The :attribute may not be greater than :max characters.',
        'array' => 'The :attribute may not have more than :max items.',
    ],
    'mimes_with_specified' => 'The :attribute must be a file of type: :values. Given type: :type',
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'multiple_of' => 'The :attribute must be a multiple of :value',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'The password is incorrect.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
    'regex_not_alpha_symbol' => 'The field cannot contain letters.',
    'required' => 'The :attribute field is required.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid zone.',
    'unique' => 'The :attribute has already been taken.',
    'unique_email' => 'Email is already in use.',
    'unique_phone' => 'Phone number is already in use.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute format is invalid.',
    'uuid' => 'The :attribute must be a valid UUID.',

    'custom' => [
        'duplicate_serial_numbers' => 'Have the same serial numbers',
        'regex_format' => 'The :attribute does not match the format :format.',
        'timestamp' => 'Date is invalid',
        'sms_token_invalid' => 'SMS code is invalid or expired',
        'serial-number' => 'Serial number - :serial - not found',
        'unit-serial-number' => 'The given serial number of the device is invalid.',
        'unit-serial-number-used' => 'This device serial number is already in use.',
        'fcm_token_invalid' => 'The FCM token is invalid.',
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'about' => [
            'page' => [
                'cant_disable' => 'The page can not disable. This page is used in active menu item.',
                'cant_delete' => 'The page can not delete. This page is used in menu item.',
            ]
        ],
        'lang' => [
            'exist-languages' => 'Argument :attribute must be existing system locale',
        ],
        'password' => [
            'password-rule' => 'The password must contain from 8 to 250 characters, at least one number and one Latin letter.'
        ],
        'name' => [
            'name-rule' => 'Argument :attribute should contains from 2 to 250 symbols'
        ],
        'reset_password' => [
            'time' => 'Password reset timed out',
            'user' => 'User is not found',
            'code' => 'Invalid password reset link',
        ],
        'catalog' => [
            'categories' => [
                'main' => 'Only :count categories can be displayed as main categories on the homepage.',
            ],
            'features' => [
                'display_in_web' => 'Only :count features can be displayed as main features on the web site.',
            ],
            'serial_numbers' => [
                'not_found' => 'Serial number not found.'
            ],
            'solutions' => [
                'incorrect_count_zones' => 'You set incorrect number of zones.',
                'duplicate_count_zones' => 'You set schemas for duplicate count zones.',
                'indoors_not_found' => 'Some indoors from default schema not found.',
                'indoor_not_connected' => 'Indoor from default schema does not connected for outdoor. Indoor: :indoor.',
                'incorrect_schema_btu' => 'You set incorrect schema. Sum BTUs of indoors is to much. Count zones: :count_zones.',
                'incorrect_btu' => 'You set incorrect BTU value.',
                'outdoor_not_found' => 'No outdoors were found for your parameters.',
                'multi_indoors_cant_search' => 'We could not search any available indoors for your selected parameters. Try to change outdoor BTU or count of zones.',
                'series_not_found' => 'We could not search any series for you selected parameters.',
                'btu_not_found' => 'We could not search any BTU for you selected parameters.',
                'change_indoor_not_found' => 'Indoor product was not found for zone: :zone.',
                'cant_change_type_and_delete' => 'This solution settings are connect to the other product and can not be disable from it. Please change parent solution setting at first. Product name: :product.',
            ],
        ],
        'order' => [
            'order_category_used' => 'The order category is used in some orders.',
            'order_part_incorrect_description' => 'Incorrect order part description.',
            'orders_have_this_delivery_type' => 'Some orders have this delivery type.',
            'orders_not_found' => 'Order not found.',
            'order_shipping_assigned_trk_number' => 'Trk number was assigned to the order.',
            'order_part_price_is_required' => 'Order part price required with payment data.',
            'order_cant_paid' => 'You can not pay for this order.',
        ],
        'project' => [
            'forbidden' => 'Project not found.'
        ],
        'payment' => [
            'something_went_wrong' => 'Something went wrong.',
            'order_approved' => 'The order has already been approved.',
        ],
        'support_request' => [
            'subject_used_in_requests' => 'The subject is used in some requests.',
            'not_found' => 'Request not found.',
        ],
        'utilities' => [
            'upload' => [
                'unsupported_model' => '":model_type" does not support uploading images.'
            ],
            'sorting' => [
                'model_object_not_found' => 'Model object with ID: :id not found.'
            ]
        ],
        'chat_menu' => [
            'incorrect_sub_menu' => 'Incorrect sub menu field'
        ]
    ],

    'attributes' => [
        'project.name' => 'Name',
        'project.systems.*.units' => 'Unit',
        'project.systems.*.units.*.id' => 'Unit id',
        'project.systems.*.units.*.serial_number' => 'Unit Serial Number',
        'system.id' => 'System Id',
        'system.units.*' => 'Unit',
        'system.units.*.serial_number' => 'Unit Serial Number',
        'faq.id' => 'Id',
        'faq.translations' => 'Translations',
        'display_in_web' => 'Display in web',
        'display_in_mobile' => 'Display in mobile',
        'order.parts.*' => 'Order parts',
        'first_name' => 'First name',
        'last_name' => 'Last name',
    ],

    'translates_array_validation_failed' => 'Incomplete Translations',

    'sorting' => [
        'incorrect-parameter' => 'Invalid sort argument.',
        'incorrect-direction' => 'Incorrect sorting direction.',
        'incorrect-field' => 'Incorrect sort field.',
    ],

    'all_values_are_not_unique' => 'Values by field :field are not unique in given data.',

    'dealer' => [
        'not_compare_email' => 'Your email [:email] does not match a contact email [:contact_email] '
    ],
    'company' => [
        'not_register' => "The company is not registered"
    ]
];
