<?php

use App\Models\Vehicles\TruckDriverHistory;
use App\Models\Vehicles\VehicleDriverHistory;

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

    'transaction_date_validation' => 'Invalid date format. YYYY-MM-DD hh:mm:ss',
    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute may only contain letters.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute may only contain letters and numbers.',
    'alpha_spaces' => 'The :attribute may only contain letters and spaces.',
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
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'The password is incorrect.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
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
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute format is invalid.',
    'uuid' => 'The :attribute must be a valid UUID.',
    'role_not_exists' => 'Role with this id is not exists.',
    'permission_not_exists' => 'Permission with this name is not exists.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'user' => [
            'not_belongs_company' => 'The user does not belong to the company.'
        ],
        'phone' => [
            'regex' => 'Phone must be correct usa number.',
        ],
        'phones.*.number' => [
            'regex' => 'Phone must be correct usa number.',
        ],
        'password_confirmation' => [
            'same' => 'The password confirmation and new password must match.',
        ],
        'password' => [
            'regex' => 'New password must contain at least one english letter, at least one number, and be longer than eight characters.',
            'different' => 'New password and current password must be different.',
        ],
        'vehicle_vin_exists' => 'Vehicle with the same vin already added to order :load_id',
        'recipient_email.*.value' => [
            'required_if' => 'The :attribute field is required.',
        ],
        'recipient_fax' => [
            'required_if' => 'The :attribute field is required.',
        ],
        'parser' => [
            'file_is_not_identify' => 'PDF file not identify!',
            'file_error' => "This PDF file can't be imported: text is not selectable, file either does not contain selectable text or contains only an image.",
            'two_destination_data' => 'PDF file must have only one pickup/delivery info.',
        ],
        'gps' => [
            'device' => [
                'cant_change_company_active_device' => 'Can\'t change company because device is active'
            ],
            'device_subscription' => [
                'field_for_device_subscription' => 'The field ":field" is available if you have a gps device subscription',
                'next_rate_same_current_rate' => 'Current GPS rate and GPS rate for the next billing period for the next billing period cannot be the same',
                'company_cancel_subscription' => 'This company has requested to cancel GPS subscription'
            ]
        ],
        'vehicle' => [
            'driver_history' => [
                'start_at' => "The date - :date cannot be the current day or more than ". VehicleDriverHistory::ADD_HISTORY_DAYS_PAST ." days in the past."
            ]
        ],
        'company' => [
            'cancel_subscription' => 'This company has requested to cancel GPS subscription'
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
        'question_en' => 'Question',
        'question_ru' => 'Question',
        'question_es' => 'Question',
        'answer_en' => 'Answer',
        'answer_ru' => 'Answer',
        'answer_es' => 'Answer',
        'load_id' => 'Load ID',
        'invoice_id' => 'Invoice ID',
        'full_name' => 'Name',
        'pickup_contact.full_name' => 'Name',
        'delivery_contact.full_name' => 'Name',
        'shipper_contact.full_name' => 'Name',
        'role_id' => 'Role',
        'email' => 'Email',
        'contact_email' => 'Email',
        'recipient_email.*.value' => 'Email',
        'state_id' => 'State',
        'pickup_contact.state_id' => 'State',
        'delivery_contact.state_id' => 'State',
        'shipper_contact.state_id' => 'State',
        'zip' => 'Zip',
        'pickup_contact.zip' => 'Zip',
        'delivery_contact.zip' => 'Zip',
        'shipper_contact.zip' => 'Zip',
        'city' => 'City',
        'pickup_contact.city' => 'City',
        'delivery_contact.city' => 'City',
        'shipper_contact.city' => 'City',
        'timezone' => 'Timezone',
        'pickup_contact.timezone' => 'Timezone',
        'delivery_contact.timezone' => 'Timezone',
        'shipper_contact.timezone' => 'Timezone',
        'type_id' => 'Type',
        'pickup_contact.type_id' => 'Contact Type',
        'delivery_contact.type_id' => 'Contact Type',
        'shipper_contact.type_id' => 'Contact Type',
        'phones.*.number' => 'Phone',
        'contact_phone' => 'Phone',
        'contact_phones.*.number' => 'Phone',
        'insurance_agent_phone' => 'Agent phone',
        'insurance_deductible' => 'Deductible',
        'notification_emails.*.value' => 'Notification emails',
        'receive_bol_copy_emails.*.value' => 'Delivery confirmation emails',
        'insurance_agent_name' => 'Agent name',
        'insurance_expiration_date' => 'Expiration date',
        'type_id' => 'Contact type',
        'working_hours' => 'Schedule',
        'pickup_contact.working_hours' => 'Schedule',
        'delivery_contact.working_hours' => 'Schedule',
        'shipper_contact.working_hours' => 'Schedule',
        'pickup_contact.address' => 'Address',
        'delivery_contact.address' => 'Address',
        'shipper_contact.address' => 'Address',
        'vehicles.*.make' => 'Make',
        'vehicles.*.model' => 'Model',
        'vehicles.*.type_id' => 'Type',

        'payment.total_carrier_amount' => 'Total amount',
        'payment.customer_payment_amount' => 'Amount',
        'payment.customer_payment_method_id' => 'Payment method',
        'payment.customer_payment_location' => 'Payment location',
        'payment.broker_payment_amount' => 'Amount',
        'payment.broker_payment_method_id' => 'Payment method',
        'payment.broker_payment_days' => 'Number of days',
        'payment.broker_payment_begins' => 'Terms begin on',
        'payment.broker_fee_amount' => 'Amount',
        'payment.broker_fee_method_id' => 'Payment method',
        'payment.broker_fee_days' => 'Number of days',
        'payment.broker_fee_begins' => 'Terms begin on',

        'expenses.*.type_id' => 'Type',
        'expenses.*.price' => 'Amount',
        'expenses.*.date' => 'Date',
        'comment' => 'Comment',
        'pickup_contact.phones.*.number' => 'Phone',
        'delivery_contact.phones.*.number' => 'Phone',
        'shipper_contact.phones.*.number' => 'Phone',
        'pickup_contact.phone' => 'Phone',
        'delivery_contact.phone' => 'Phone',
        'shipper_contact.phone' => 'Phone',

        'paid_method_id' => 'Payment method',
        'reference_number' => 'Reference number',
        'receipt_date' => 'Receipt date',
        'recipient_fax' => 'Fax',

        'send_via' => 'Send to',
        'usdot-not-exists' => 'Usdot not exists',

        'expenses_before.*.type' => 'Type',
        'expenses_after.*.type' => 'Type',
        'bonuses.*.type' => 'Type',
        'expenses_before.*.price' => 'Price',
        'expenses_after.*.price' => 'Price',
        'bonuses.*.price' => 'Price',

        'customer_full_name' => 'Customer full name',

        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'phone' => 'Phone',

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

        'color' => 'Color',
        'type' => 'Type',

        'tags' => 'Tags',
        'tags.*' => 'Tag',

        'stock_number' => 'Stock Number',
        'quantity' => 'Quantity',
        'price_wholesale' => 'Cost',
        'price_retail' => 'Price',
        'category_id' => 'Category',
        'supplier_id' => 'Supplier',
        'notes' => 'Additional notes',
        'quantity_comment' => 'Describe',

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
        'driver_license.license_number' => 'License number',
        'driver_license.issuing_state_id' => 'State',
        'driver_license.issuing_date' => 'Issuing date',
        'driver_license.expiration_date' => 'Expiration date',
        'driver_license.category' => 'Category',
        'driver_license.category_name' => 'Category name',
        'driver_license.attached_document' => 'Attached document',
        'previous_driver_license.license_number' => 'License number',
        'previous_driver_license.issuing_state_id' => 'State',
        'previous_driver_license.issuing_date' => 'Issuing date',
        'previous_driver_license.expiration_date' => 'Expiration date',
        'previous_driver_license.category' => 'Category',
        'previous_driver_license.category_name' => 'Category name',
        'previous_driver_license.attached_document' => 'Attached document',
        'medical_card.card_number' => 'Card number',
        'medical_card.issuing_date' => 'Issuing date',
        'medical_card.expiration_date' => 'Expiration date',
        'medical_card.medical_card_document' => 'Document',
        'mvr.reported_date' => 'Reported date',
        'company_info.name' => 'Company Name',
        'company_info.ein' => 'EIN',
        'company_info.address' => 'Address',
        'company_info.city' => 'City',
        'company_info.zip' => 'ZIP',
        'driver_salary_contact_info.email' => 'Email',
        'driver_salary_contact_info.phones.*.number' => 'Phone',
        'driver_salary_contact_info.phones.*.extension' => 'Phone Extension',
    ],

    'current_password' => 'Wrong current password',
    'deactivate_user_before_deleting' => 'Deactivate user before deleting',

    'company_id' => 'Company',
    'imei' => 'IMEI',
    'invalid_card_number' => 'Invalid card number',
    'invalid_driver_name' => 'Invalid driver name',
    'invalid_location' => 'Invalid data format. This field accepts only text.',
    'invalid_fueling_only_decimals' => 'Invalid data format. This field accepts only integers and decimals.',
    'invalid_fueling_only_text' => 'Invalid data format. This field accepts only text.',
];
