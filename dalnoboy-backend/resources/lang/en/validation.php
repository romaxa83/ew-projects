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
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute format is invalid.',
    'uuid' => 'The :attribute must be a valid UUID.',

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'lang' => [
            'exist-languages' => 'Argument :attribute must be existing system locale',
        ],
        'password' => [
            'password-rule' => 'Password should contains from 8 to 250 symbols, at list one digit and one latin letter'
        ],
        'name' => [
            'name-rule' => 'Argument :attribute should contains from 2 to 250 symbols'
        ],
        'forward_number' => [
            'forward_number-rule' => 'Forward number should contains from 3 to 8 digits (existing inner phone of company) or from 10 to 20 digits - outer phone number',
        ],
        'delete-rule' => 'You cannot delete, there is a connection',
        'reset_password' => [
            'time' => 'Password reset timed out',
            'user' => 'User is not found',
            'code' => 'Invalid password reset link',
        ],
        'branches' => [
            'similar_branch' => 'You already have branch with similar setting (city/region/address/phones). Branch name: :branch_name',
            'branch_has_employees_yet' => 'The branch has have some employees yet',
        ],
        'utilities' => [
            'sort_direction' => 'You set incorrect sort direction',
            'sort_field' => 'You set incorrect sort field',
            'import_file_incorrect' => 'The file has incorrect structure',
            'nothing_to_export' => 'There is no data',
        ],
        'users' => [
            'uniq_email' => 'You already create user with the same email',
        ],
        'managers' => [
            'uniq' => 'You already create manager with the same data',
            'has_clients' => 'The manager still has clients',
        ],
        'admins' => [
            'uniq_email' => 'You already create admin with the same email. Admin: :admin',
            'can_not_delete_super_admin' => 'An administrator with the Super Admin role can not be deleted',
        ],
        'clients' => [
            'incorrect_edrpou' => 'You set incorrect EDRPOU code',
            'incorrect_inn' => 'You set incorrect INN code',
            'only_inn_or_edrpou' => 'You can set only INN or EDRPOU code',
            'uniq' => 'You already create a client with the same INN code or EDRPOU code',
        ],
        'drivers' => [
            'uniq' => 'You already create a driver with the same phones or email',
        ],
        'localization' => [
            'translate_exists' => 'You already have translation with the same attributes',
        ],
        'vehicles' => [
            'schemas' => [
                'original_not_found' => 'Original schema not found',
                'wheels_not_found' => 'Some wheels not found in original schema',
                'similar_schema' => 'You already have the schema with the similar list of wheels',
                'similar_schema_name' => 'You already have the schema with the similar name',
                'schema_has_vehicles' => 'Scheme registered in vehicles',
            ],
            'not_uniq_state_number' => 'You already have vehicle with the same state number',
            'not_uniq_vin' => 'You already have vehicle with the same vin',
            'incorrect_vehicle_data' => 'You set incorrect vehicle data',
            'vehicle_connect_to_inspection' => 'Unable to perform action. The vehicle has inspections',
        ],
        'has_related_entities' => 'Can\'t delete entity with related entities',
        'same_entity_exists' => 'Record with same data already exists',
        'dictionaries' => [
            'not_uniq_make' => 'A vehicle brand with this name has already been created',
            'not_uniq_model' => 'A model of the vehicle make with this name has already been created',
        ]
    ],

    'attributes' => [
        'forward_number' => 'Forward on phone number',
        'forward_status' => 'Forwarding',
        'prefix' => 'Prefix',
    ],

    'user_in_company_not_exists' => 'There is no such employee in this company.',

    'sorting' => [
        'incorrect-parameter' => 'Invalid sort argument.',
        'incorrect-direction' => 'Incorrect sorting direction.',
        'incorrect-field' => 'Incorrect sort field.',
    ],

    'translates_array_validation_failed' => 'Incomplete Translations',
];
