<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute field must be accepted.',
    'accepted_if' => 'The :attribute field must be accepted when :other is :value.',
    'active_url' => 'The :attribute field must be a valid URL.',
    'after' => 'The :attribute field must be a date after :date.',
    'after_or_equal' => 'The :attribute field must be a date after or equal to :date.',
    'alpha' => 'The :attribute field must only contain letters.',
    'alpha_dash' => 'The :attribute field must only contain letters, numbers, dashes, and underscores.',
    'alpha_spaces' => 'The :attribute may only contain letters and spaces.',
    'alpha_num' => 'The :attribute field must only contain letters and numbers.',
    'array' => 'The :attribute field must be an array.',
    'ascii' => 'The :attribute field must only contain single-byte alphanumeric characters and symbols.',
    'before' => 'The :attribute field must be a date before :date.',
    'before_or_equal' => 'The :attribute field must be a date before or equal to :date.',
    'between' => [
        'array' => 'The :attribute field must have between :min and :max items.',
        'file' => 'The :attribute field must be between :min and :max kilobytes.',
        'numeric' => 'The :attribute field must be between :min and :max.',
        'string' => 'The :attribute field must be between :min and :max characters.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'can' => 'The :attribute field contains an unauthorized value.',
    'confirmed' => 'The :attribute field confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute field must be a valid date.',
    'date_equals' => 'The :attribute field must be a date equal to :date.',
    'date_format' => 'The :attribute field must match the format :format.',
    'decimal' => 'The :attribute field must have :decimal decimal places.',
    'declined' => 'The :attribute field must be declined.',
    'declined_if' => 'The :attribute field must be declined when :other is :value.',
    'different' => 'The :attribute field and :other must be different.',
    'digits' => 'The :attribute field must be :digits digits.',
    'digits_between' => 'The :attribute field must be between :min and :max digits.',
    'dimensions' => 'The :attribute field has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_end_with' => 'The :attribute field must not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute field must not start with one of the following: :values.',
    'email' => 'The :attribute field must be a valid email address.',
    'ends_with' => 'The :attribute field must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute field must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'array' => 'The :attribute field must have more than :value items.',
        'file' => 'The :attribute field must be greater than :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than :value.',
        'string' => 'The :attribute field must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute field must have :value items or more.',
        'file' => 'The :attribute field must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than or equal to :value.',
        'string' => 'The :attribute field must be greater than or equal to :value characters.',
    ],
    'hex_color' => 'The :attribute field must be a valid hexadecimal color.',
    'image' => 'The :attribute field must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field must exist in :other.',
    'integer' => 'The :attribute field must be an integer.',
    'ip' => 'The :attribute field must be a valid IP address.',
    'ipv4' => 'The :attribute field must be a valid IPv4 address.',
    'ipv6' => 'The :attribute field must be a valid IPv6 address.',
    'json' => 'The :attribute field must be a valid JSON string.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'array' => 'The :attribute field must have less than :value items.',
        'file' => 'The :attribute field must be less than :value kilobytes.',
        'numeric' => 'The :attribute field must be less than :value.',
        'string' => 'The :attribute field must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute field must not have more than :value items.',
        'file' => 'The :attribute field must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be less than or equal to :value.',
        'string' => 'The :attribute field must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute field must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute field must not have more than :max items.',
        'file' => 'The :attribute field must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute field must not be greater than :max.',
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimes' => 'The :attribute field must be a file of type: :values.',
    'mimetypes' => 'The :attribute field must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute field must have at least :min items.',
        'file' => 'The :attribute field must be at least :min kilobytes.',
        'numeric' => 'The :attribute field must be at least :min.',
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'missing' => 'The :attribute field must be missing.',
    'missing_if' => 'The :attribute field must be missing when :other is :value.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => 'The :attribute field must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute field format is invalid.',
    'numeric' => 'The :attribute field must be a number.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'present_if' => 'The :attribute field must be present when :other is :value.',
    'present_unless' => 'The :attribute field must be present unless :other is :value.',
    'present_with' => 'The :attribute field must be present when :values is present.',
    'present_with_all' => 'The :attribute field must be present when :values are present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute field format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute field must match :other.',
    'size' => [
        'array' => 'The :attribute field must contain :size items.',
        'file' => 'The :attribute field must be :size kilobytes.',
        'numeric' => 'The :attribute field must be :size.',
        'string' => 'The :attribute field must be :size characters.',
    ],
    'starts_with' => 'The :attribute field must start with one of the following: :values.',
    'timezone' => 'The :attribute field must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'uppercase' => 'The :attribute field must be uppercase.',
    'url' => 'The :attribute field must be a valid URL.',
    'ulid' => 'The :attribute field must be a valid ULID.',
    'uuid' => 'The :attribute field must be a valid UUID.',

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'phone' => [
            'phone_rule' => 'Phone must be correct usa number.'
        ],
        'password' => [
            'password_rule' => 'Password should contains from 8 to 250 symbols, at list one digit and one latin letter'
        ],
        'full_name' => [
            'alpha_spaces' => 'The full name can contain only English characters.'
        ],
        'quantity' => [
            'must_be_integer' => 'The quantity must be integer value.'
        ],
        'customer' => [
            'exist' => 'This customer already exists.',
            'exist_and_has_manager' => 'This customer already exists. The customer is assigned to :sales_manager_name (:sales_manager_email)'
        ],
        'inventory' => [
            'price_less_min_price' => 'The price (:price) is less than the min. price (:min_price).',
        ],
        'order' => [
            'bs' => [
                'inventory_has_price' => 'The Price field of Inventory must be filled',
                'not_enough_inventory_for_restore' => 'Not enough inventory for this order. You will need to edit the order before restoring it.'
            ],
            'parts' => [
                'few_quantities' => 'The required quantity is not in stock.',
                'has_overload' => 'The order has a heavy product, the delivery option can only be pickup',
                'not_items_and_delivery_method' => 'Before adding shipping methods you need to add items and delivery method.',
                'discounted_price_cannot_be_less_than_min_price' => 'The discounted price (:discounted_price) cannot be less than the min. price (:min_price).',
            ]
        ],
        'user' => [
            'role' => [
                'mechanic_not_found' => 'Mechanic not found.',
                'sales_manager_not_found' => 'Sales manager not found.',
                'not_belong_to_role' => 'The user does not belong to the role - :role_name'
            ],
            'is_not_active' => 'User is not active.'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'amount' => 'Amount',
        'nested' => [
            'tags' => [
                '*' => 'Tag'
            ],
        ],
        'slug' => 'Slug',
        'load_id' => 'Load ID',
        'invoice_id' => 'Invoice ID',
        'full_name' => 'Name',
        'role_id' => 'Role',
        'email' => 'Email',
        'contact_email' => 'Email',
        'state_id' => 'State',
        'zip' => 'Zip',
        'city' => 'City',
        'state' => 'State',
        'timezone' => 'Timezone',
        'phones.*.number' => 'Phone',
        'contact_phone' => 'Phone',
        'contact_phones.*.number' => 'Phone',
        'type_id' => 'Contact type',
        'vehicles.*.make' => 'Make',
        'vehicles.*.model' => 'Model',
        'vehicles.*.type_id' => 'Type',

        'customer_full_name' => 'Customer full name',
        'duration' => 'Duration',
        'hourly_rate' => 'Hourly rate',

        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'phone' => 'Phone',
        'phone_extension' => 'Phone Extension',
        'password' => 'Password',
        'password confirmation' => 'Password confirmation',
        'address' => 'Address',
        'billing_phone' => 'Billing phone',

        'parent_id' => 'Parent',
        'display_menu' => 'Display menu',
        'position' => 'Position',

        'url' => 'URL',
        'contacts' => 'Contacts',
        'contacts.*.name' => 'Name',
        'contacts.*.email' => 'Email',
        'contacts.*.phone' => 'Phone',
        'contacts.*.phones.*.number' => 'Phone',
        'contacts.*.phones.*.extension' => 'Phone Extension',
        'contacts.*.position' => 'Position',
        'contacts.*.emails.*' => 'Emails',
        'contacts.*.emails.*.value' => 'Email',

        'name'  => 'Name',
        'status'  => 'Status',

        'color' => 'Color',
        'type' => 'Type',

        'tags' => 'Tags',
        'tags.*' => 'Tag',

        'feature_id' => 'Feature',
        'features.*.feature_id' => 'Feature',
        'value_id' => 'Value',
        'features.*.value_ids' => 'Value',
        'features.*.value_id' => 'Value',

        'stock_number' => 'Stock Number',
        'quantity' => 'Quantity',
        'price_wholesale' => 'Cost',
        'price_retail' => 'Price',
        'old_price' => 'Price old',
        'min_limit_price' => 'Min limit price',
        'min_limit' => 'Min limit',
        'category_id' => 'Category',
        'brand_id' => 'Brand',
        'supplier_id' => 'Supplier',
        'notes' => 'Additional notes',
        'quantity_comment' => 'Describe',
        'for_shop' => 'For shop',
        'sold' => 'sold',
        'article_number' => 'Article number',
        'package_type' => 'Package type',

        'length' => 'Length',
        'width' => 'Width',
        'height' => 'Height',
        'weight' => 'Weight',

        'vin' => 'VIN',
        'unit_number' => 'Unit Number',
        'make' => 'Make',
        'model' => 'Model',
        'year' => 'Year',
        'license_plate' => 'License Plate',
        'temporary_plate' => 'Temporary Plate',

        'truck_id' => 'Truck',
        'trailer_id' => 'Trailer',
        'discount' => 'Discount',
        'tax_inventory' => ' Pаrts tax',
        'tax_labor' => 'Labor tax',
        'implementation_date' => 'Date, time',
        'mechanic_id' => 'Mechanic',
        'types_of_work' => 'Types Of Work',
        'types_of_work.*.name' => 'Name',
        'types_of_work.*.save_to_the_list' => 'Save type of work to the list',
        'types_of_work.*.duration' => 'Duration',
        'types_of_work.*.hourly_rate' => 'Hourly rate',
        'types_of_work.*.inventories' => 'Parts',
        'types_of_work.*.inventories.*.id' => 'Part',
        'types_of_work.*.inventories.*.quantity' => 'Quantity',

        'accept_decimals' => 'Accept Decimals',

        'unit_id' => 'Unit of measurement',

        'purchase.cost' => 'Cost',
        'purchase.quantity' => 'Quantity',
        'purchase.invoice_number' => 'Invoice No',
        'purchase.date' => 'Date',
        'purchase' => [
            'cost' => 'Cost',
            'quantity' => 'Quantity',
            'invoice_number' => 'Invoice No',
            'date' => 'Date',
        ],

        'invoice_number' => 'Invoice No',
        'price' => 'Price',
        'cost' => 'Cost',
        'describe' => 'Describe',
        'date' => 'Date',
        'due_date' => 'Due Date',
        'payment_date' => 'Payment Date',
        'payment_method' => 'Payment Method',
        'company_name' => 'Company Name',
        'tax' => 'Tax',
        'is_default' => 'Default',

        'sales_manager_id' => 'Sales Manager',
        'customer_id' => 'Customer',

        'ecommerce_company_name' => 'Company Name',
        'ecommerce_address' => 'Address',
        'ecommerce_city' => 'City',
        'ecommerce_state_id' => 'State',
        'ecommerce_zip' => 'Zip',
        'ecommerce_phone' => 'Phone',
        'ecommerce_email' => 'Email',
        'ecommerce_billing_phone' => 'Phone',

        'items' => 'Items',
        'shipping_method' => 'Shipping method',
        'source' => 'Source',
    ],
];
