<?php

return [
    'accepted' => ':attribute debe ser aceptado.',
    'accepted_if' => ':attribute debe ser aceptado cuando :other sea :value.',
    'active_url' => ':attribute no es una URL válida.',
    'after' => ':attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => ':attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => ':attribute sólo debe contener letras.',
    'alpha_dash' => ':attribute sólo debe contener letras, números, guiones y guiones bajos.',
    'alpha_num' => ':attribute sólo debe contener letras y números.',
    'array' => ':attribute debe ser un conjunto.',
    'before' => ':attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => ':attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'array' => ':attribute tiene que tener entre :min - :max elementos.',
        'file' => ':attribute debe pesar entre :min - :max kilobytes.',
        'numeric' => ':attribute tiene que estar entre :min - :max.',
        'string' => ':attribute tiene que tener entre :min - :max caracteres.',
    ],
    'boolean' => 'El campo :attribute debe tener un valor verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña es incorrecta.',
    'date' => ':attribute no es una fecha válida.',
    'date_equals' => ':attribute debe ser una fecha igual a :date.',
    'date_format' => ':attribute no corresponde al formato :format.',
    'declined' => ':attribute debe ser rechazado.',
    'declined_if' => ':attribute debe ser rechazado cuando :other sea :value.',
    'different' => ':attribute y :other deben ser diferentes.',
    'digits' => ':attribute debe tener :digits dígitos.',
    'digits_between' => ':attribute debe tener entre :min y :max dígitos.',
    'dimensions' => 'Las dimensiones de la imagen :attribute no son válidas.',
    'distinct' => 'El campo :attribute contiene un valor duplicado.',
    'email' => ':attribute no es un correo válido.',
    'ends_with' => 'El campo :attribute debe finalizar con uno de los siguientes valores: :values',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => ':attribute es inválido.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'filled' => 'El campo :attribute es obligatorio.',
    'gt' => [
        'array' => 'El campo :attribute debe tener más de :value elementos.',
        'file' => 'El campo :attribute debe tener más de :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'string' => 'El campo :attribute debe tener más de :value caracteres.',
    ],
    'gte' => [
        'array' => 'El campo :attribute debe tener como mínimo :value elementos.',
        'file' => 'El campo :attribute debe tener como mínimo :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser como mínimo :value.',
        'string' => 'El campo :attribute debe tener como mínimo :value caracteres.',
    ],
    'image' => ':attribute debe ser una imagen.',
    'in' => ':attribute es inválido.',
    'in_array' => 'El campo :attribute no existe en :other.',
    'integer' => ':attribute debe ser un número entero.',
    'ip' => ':attribute debe ser una dirección IP válida.',
    'ipv4' => ':attribute debe ser una dirección IPv4 válida.',
    'ipv6' => ':attribute debe ser una dirección IPv6 válida.',
    'json' => 'El campo :attribute debe ser una cadena JSON válida.',
    'lt' => [
        'array' => 'El campo :attribute debe tener menos de :value elementos.',
        'file' => 'El campo :attribute debe tener menos de :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser menor que :value.',
        'string' => 'El campo :attribute debe tener menos de :value caracteres.',
    ],
    'lte' => [
        'array' => 'El campo :attribute debe tener como máximo :value elementos.',
        'file' => 'El campo :attribute debe tener como máximo :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser como máximo :value.',
        'string' => 'El campo :attribute debe tener como máximo :value caracteres.',
    ],
    'mac_address' => 'The :attribute must be a valid MAC address.',
    'max' => [
        'array' => ':attribute no debe tener más de :max elementos.',
        'file' => ':attribute no debe ser mayor que :max kilobytes.',
        'numeric' => ':attribute no debe ser mayor que :max.',
        'string' => ':attribute no debe ser mayor que :max caracteres.',
    ],
    'mimes' => ':attribute debe ser un archivo con formato: :values.',
    'mimetypes' => ':attribute debe ser un archivo con formato: :values.',
    'min' => [
        'array' => ':attribute debe tener al menos :min elementos.',
        'file' => 'El tamaño de :attribute debe ser de al menos :min kilobytes.',
        'numeric' => 'El tamaño de :attribute debe ser de al menos :min.',
        'string' => ':attribute debe contener al menos :min caracteres.',
    ],
    'multiple_of' => 'El campo :attribute debe ser múltiplo de :value',
    'not_in' => ':attribute es inválido.',
    'not_regex' => 'El formato del campo :attribute no es válido.',
    'numeric' => ':attribute debe ser numérico.',
    'password' => 'La contraseña es incorrecta.',
    'present' => 'El campo :attribute debe estar presente.',
    'prohibited' => 'El campo :attribute está prohibido.',
    'prohibited_if' => 'El campo :attribute está prohibido cuando :other es :value.',
    'prohibited_unless' => 'El campo :attribute está prohibido a menos que :other sea :values.',
    'prohibits' => 'El campo :attribute prohibe que :other esté presente.',
    'regex' => 'El formato de :attribute es inválido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_unless' => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all' => 'El campo :attribute es obligatorio cuando :values están presentes.',
    'required_without' => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values está presente.',
    'same' => ':attribute y :other deben coincidir.',
    'size' => [
        'array' => ':attribute debe contener :size elementos.',
        'file' => 'El tamaño de :attribute debe ser :size kilobytes.',
        'numeric' => 'El tamaño de :attribute debe ser :size.',
        'string' => ':attribute debe contener :size caracteres.',
    ],
    'starts_with' => 'El campo :attribute debe comenzar con uno de los siguientes valores: :values',
    'string' => 'El campo :attribute debe ser una cadena de caracteres.',
    'timezone' => ':Attribute debe ser una zona horaria válida.',
    'unique' => 'El campo :attribute ya ha sido registrado.',
    'uploaded' => 'Subir :attribute ha fallado.',
    'url' => ':Attribute debe ser una URL válida.',
    'uuid' => 'El campo :attribute debe ser un UUID válido.',

    'custom' => [
        'duplicate_serial_numbers' => 'Tienen los mismos números de serie',
        'date_format' => 'The :attribute does not match the format :format.',
        'timestamp' => 'La fecha no es válida',
        'sms_token_invalid' => 'El código SMS no es válido o ha caducado',
        'serial-number' => 'Número de serie - :serial - no encontrado',
        'unit-serial-number' => 'El número de serie proporcionado del dispositivo no es válido.',
        'unit-serial-number-used' => 'El número de serie de este dispositivo ya está en uso.',
        'fcm_token_invalid' => 'El token de FCM no es válido.',
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'about' => [
            'page' => [
                'cant_disable' => 'La página no se puede deshabilitar. Esta página se utiliza en el elemento de menú activo.',
                'cant_delete' => 'La página no se puede eliminar. Esta página se utiliza en el elemento del menú.',
            ]
        ],
        'lang' => [
            'exist-languages' => 'Argumento :attribute debe ser una configuración regional del sistema existente',
        ],
        'password' => [
            'password-rule' => 'La contraseña debe contener de 8 a 250 caracteres, al menos un número y una letra latina.'
        ],
        'name' => [
            'name-rule' => 'Argumento :attribute debe contener de 2 a 250 símbolos'
        ],
        'reset_password' => [
            'time' => 'Se agotó el tiempo de restablecimiento de contraseña',
            'user' => 'El usuario no se encuentra',
            'code' => 'Enlace de restablecimiento de contraseña no válido',
        ],
        'catalog' => [
            'features' => [
                'display_in_web' => 'Solo se pueden mostrar :count características como características principales en el sitio web.',
            ],
            'serial_numbers' => [
                'not_found' => 'Número de serie no encontrado.'
            ],
            'solutions' => [
                'incorrect_count_zones' => 'Configuró un número incorrecto de zonas.',
                'incorrect_btu' => 'Configuró un valor de BTU incorrecto.',
                'outdoor_not_found' => 'No se encontraron exteriores para sus parámetros.',
                'multi_indoors_cant_search' => 'No pudimos buscar ningún interior disponible para los parámetros seleccionados. Intente cambiar el BTU exterior o el conteo de zonas.',
                'series_not_found' => 'No pudimos buscar ninguna serie para sus parámetros seleccionados.',
                'btu_not_found' => 'No pudimos buscar ninguna BTU para sus parámetros seleccionados.',
                'change_indoor_not_found' => 'No se encontró el producto de interior para la zona: :zone.',
                'cant_change_type_and_delete' => 'La configuración de esta solución se conecta al otro producto y no se puede deshabilitar desde él. Por favor, cambie el producto principal al principio. Nombre del producto: :product.',
            ],
        ],
        'order' => [
            'order_category_used' => 'La categoría de pedido se utiliza en algunos pedidos.',
            'order_part_incorrect_description' => 'Descripción incorrecta de la pieza del pedido.',
            'orders_have_this_delivery_type' => 'Algunos pedidos tienen este tipo de entrega.',
            'orders_not_found' => 'Pedido no encontrado.',
            'order_shipping_assigned_trk_number' => 'Se asignó el número de trk a la orden.',
            'order_part_price_is_required' => 'Pedir precio de pieza requerido con datos de pago.',
            'order_cant_paid' => 'No puedes pagar este pedido.',
        ],
        'project' => [
            'forbidden' => 'Proyecto no encontrado.'
        ],
        'payment' => [
            'something_went_wrong' => 'Algo salió mal.',
            'order_approved' => 'El pedido ya ha sido aprobado.',
        ],
        'support_request' => [
            'subject_used_in_requests' => 'El sujeto se utiliza en algunas solicitudes.',
            'not_found' => 'Solicitud no encontrada.',
        ],
        'chat_menu' => [
            'incorrect_sub_menu' => 'Incorrect sub menu field'
        ]
    ],

    'attributes' => [
        'project.name' => 'Nombre',
        'project.systems.*.units' => 'Unidad',
        'project.systems.*.units.*.id' => 'Unidad id',
        'project.systems.*.units.*.serial_number' => 'Número de serie de la unidad',
        'system.id' => 'System Id',
        'system.units.*' => 'Unidad',
        'system.units.*.serial_number' => 'Número de serie',
        'faq.id' => 'Id',
        'faq.translations' => 'Translations',
        'display_in_web' => 'Display in web',
        'display_in_mobile' => 'Display in mobile',
        'order.parts.*' => 'Order parts',
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
    ]
];
