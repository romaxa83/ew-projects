<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Языковые ресурсы для проверки значений
    |--------------------------------------------------------------------------
    |
    | Последующие языковые строки содержат сообщения по-умолчанию, используемые
    | классом, проверяющим значения (валидатором). Некоторые из правил имеют
    | несколько версий, например, size. Вы можете поменять их на любые
    | другие, которые лучше подходят для вашего приложения.
    |
    */

    'accepted' => 'Вы должны принять :attribute.',
    'active_url' => 'Поле :attribute содержит недействительный URL.',
    'after' => 'В поле :attribute должна быть дата после :date.',
    'after_or_equal' => 'В поле :attribute должна быть дата после или равняться :date.',
    'alpha' => 'Поле :attribute может содержать только буквы.',
    'alpha_dash' => 'Поле :attribute может содержать только буквы, цифры, дефис и нижнее подчеркивание.',
    'alpha_num' => 'Поле :attribute может содержать только буквы и цифры.',
    'array' => 'Поле :attribute должно быть массивом.',
    'before' => 'В поле :attribute должна быть дата до :date.',
    'before_or_equal' => 'В поле :attribute должна быть дата до или равняться :date.',
    'between' => [
        'numeric' => 'Поле :attribute должно быть между :min и :max.',
        'file' => 'Размер файла в поле :attribute должен быть между :min и :max Килобайт(а).',
        'string' => 'Количество символов в поле :attribute должно быть между :min и :max.',
        'array' => 'Количество элементов в поле :attribute должно быть между :min и :max.',
    ],
    'boolean' => 'Поле :attribute должно иметь значение логического типа.',
    'confirmed' => 'Поле :attribute не совпадает с подтверждением.',
    'date' => 'Поле :attribute не является датой.',
    'date_equals' => 'Поле :attribute должно быть датой равной :date.',
    'date_format' => 'Поле :attribute не соответствует формату :format.',
    'different' => 'Поля :attribute и :other должны различаться.',
    'digits' => 'Длина цифрового поля :attribute должна быть :digits.',
    'digits_between' => 'Длина цифрового поля :attribute должна быть между :min и :max.',
    'dimensions' => 'Поле :attribute имеет недопустимые размеры изображения.',
    'distinct' => 'Поле :attribute содержит повторяющееся значение.',
    'email' => 'Поле :attribute должно быть действительным электронным адресом.',
    'ends_with' => 'Поле :attribute должно заканчиваться одним из следующих значений: :values',
    'exists' => 'Выбранное значение для :attribute некорректно.',
    'file' => 'Поле :attribute должно быть файлом.',
    'filled' => 'Поле :attribute обязательно для заполнения.',
    'gt' => [
        'numeric' => 'Поле :attribute должно быть больше :value.',
        'file' => 'Размер файла в поле :attribute должен быть больше :value Килобайт(а).',
        'string' => 'Количество символов в поле :attribute должно быть больше :value.',
        'array' => 'Количество элементов в поле :attribute должно быть больше :value.',
    ],
    'gte' => [
        'numeric' => 'Поле :attribute должно быть больше или равно :value.',
        'file' => 'Размер файла в поле :attribute должен быть больше или равен :value Килобайт(а).',
        'string' => 'Количество символов в поле :attribute должно быть больше или равно :value.',
        'array' => 'Количество элементов в поле :attribute должно быть больше или равно :value.',
    ],
    'image' => 'Поле :attribute должно быть изображением.',
    'in' => 'Выбранное значение для :attribute ошибочно.',
    'in_array' => 'Поле :attribute не существует в :other.',
    'integer' => 'Поле :attribute должно быть целым числом.',
    'ip' => 'Поле :attribute должно быть действительным IP-адресом.',
    'ipv4' => 'Поле :attribute должно быть действительным IPv4-адресом.',
    'ipv6' => 'Поле :attribute должно быть действительным IPv6-адресом.',
    'json' => 'Поле :attribute должно быть JSON строкой.',
    'lt' => [
        'numeric' => 'Поле :attribute должно быть меньше :value.',
        'file' => 'Размер файла в поле :attribute должен быть меньше :value Килобайт(а).',
        'string' => 'Количество символов в поле :attribute должно быть меньше :value.',
        'array' => 'Количество элементов в поле :attribute должно быть меньше :value.',
    ],
    'lte' => [
        'numeric' => 'Поле :attribute должно быть меньше или равно :value.',
        'file' => 'Размер файла в поле :attribute должен быть меньше или равен :value Килобайт(а).',
        'string' => 'Количество символов в поле :attribute должно быть меньше или равно :value.',
        'array' => 'Количество элементов в поле :attribute должно быть меньше или равно :value.',
    ],
    'max' => [
        'numeric' => 'Поле :attribute не может быть более :max.',
        'file' => 'Размер файла в поле :attribute не может быть более :max Килобайт(а).',
        'string' => 'Количество символов в поле :attribute не может превышать :max.',
        'array' => 'Количество элементов в поле :attribute не может превышать :max.',
    ],
    'mimes' => 'Поле :attribute должно быть файлом одного из следующих типов: :values.',
    'mimetypes' => 'Поле :attribute должно быть файлом одного из следующих типов: :values.',
    'min' => [
        'numeric' => 'Поле :attribute должно быть не менее :min.',
        'file' => 'Размер файла в поле :attribute должен быть не менее :min Килобайт(а).',
        'string' => 'Количество символов в поле :attribute должно быть не менее :min.',
        'array' => 'Количество элементов в поле :attribute должно быть не менее :min.',
    ],
    'not_in' => 'Выбранное значение для :attribute ошибочно.',
    'not_regex' => 'Выбранный формат для :attribute ошибочный.',
    'numeric' => 'Поле :attribute должно быть числом.',
    'password' => 'Неверный пароль.',
    'present' => 'Поле :attribute должно присутствовать.',
    'regex' => 'Поле :attribute имеет ошибочный формат.',
    'required' => 'Поле :attribute обязательно для заполнения.',
    'required_if' => 'Поле :attribute обязательно для заполнения, когда :other равно :value.',
    'required_unless' => 'Поле :attribute обязательно для заполнения, когда :other не равно :values.',
    'required_with' => 'Поле :attribute обязательно для заполнения, когда :values указано.',
    'required_with_all' => 'Поле :attribute обязательно для заполнения, когда :values указано.',
    'required_without' => 'Поле :attribute обязательно для заполнения, когда :values не указано.',
    'required_without_all' => 'Поле :attribute обязательно для заполнения, когда ни одно из :values не указано.',
    'same' => 'Значения полей :attribute и :other должны совпадать.',
    'size' => [
        'numeric' => 'Поле :attribute должно быть равным :size.',
        'file' => 'Размер файла в поле :attribute должен быть равен :size Килобайт(а).',
        'string' => 'Количество символов в поле :attribute должно быть равным :size.',
        'array' => 'Количество элементов в поле :attribute должно быть равным :size.',
    ],
    'starts_with' => 'Поле :attribute должно начинаться из одного из следующих значений: :values',
    'string' => 'Поле :attribute должно быть строкой.',
    'timezone' => 'Поле :attribute должно быть действительным часовым поясом.',
    'unique' => 'Такое значение поля :attribute уже существует.',
    'uploaded' => 'Загрузка поля :attribute не удалась.',
    'url' => 'Поле :attribute имеет ошибочный формат.',
    'uuid' => 'Поле :attribute должно быть корректным UUID.',
    'role_not_exists' => 'Роль с указанным id не существует.',
    'permission_not_exists' => 'Разрешение с указанным id не существует.',

    /*
    |--------------------------------------------------------------------------
    | Собственные языковые ресурсы для проверки значений
    |--------------------------------------------------------------------------
    |
    | Здесь Вы можете указать собственные сообщения для атрибутов.
    | Это позволяет легко указать свое сообщение для заданного правила атрибута.
    |
    | http://laravel.com/docs/validation#custom-error-messages
    | Пример использования
    |
    |   'custom' => [
    |       'email' => [
    |           'required' => 'Нам необходимо знать Ваш электронный адрес!',
    |       ],
    |   ],
    |
    */

    'custom' => [
        'full_name' => [
            'alpha_spaces' => 'Поле :attribute может содержать только буквы и пробелы.',
        ],
        'phone' => [
            'regex' => 'Поле :attribute должно быть корректным номером телефона в США.',
        ],
        'phones.*.number' => [
            'regex' => 'Поле :attribute должно быть корректным номером телефона в США.',
        ],
        'password_confirmation' => [
            'same' => 'Подтверждение пароля и новый пароль должны совпадать.',
        ],
        'password' => [
            'regex' => 'Новый пароль должен содержать хотя бы одну английскую букву, хотя бы одно число и быть длиннее восьми символов.',
            'different' => 'Новый пароль и текущий пароль должны отличаться.',
        ],
        'vehicle_vin_exists' => 'Автомобиль с таким же VIN уже добавлен к заказу :load_id',
        'recipient_email.*.value' => [
            'required_if' => 'Поле :attribute обязательно для заполнения.',
        ],
        'recipient_fax' => [
            'required_if' => 'Поле :attribute обязательно для заполнения.',
        ],
        'parser' => [
            'file_is_not_identify' => 'PDF файл не идентифицирован!',
            'file_error' => 'Этот файл PDF нельзя импортировать: текст нельзя выделить, файл либо не содержит текста, доступного для выбора, либо содержит только изображение.',
            'two_destination_data' => 'Файл PDF должен содержать только одну информацию о получении/доставке.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Собственные названия атрибутов
    |--------------------------------------------------------------------------
    |
    | Последующие строки используются для подмены программных имен элементов
    | пользовательского интерфейса на удобочитаемые. Например, вместо имени
    | поля "email" в сообщениях будет выводиться "электронный адрес".
    |
    | Пример использования
    |
    |   'attributes' => [
    |       'email' => 'электронный адрес',
    |   ],
    |
    */

    'attributes' => [
        'question_en' => 'Вопрос',
        'question_ru' => 'Вопрос',
        'question_es' => 'Вопрос',
        'answer_en' => 'Ответ',
        'answer_ru' => 'Ответ',
        'answer_es' => 'Ответ',
        'load_id' => 'ID груза',
        'invoice_id' => 'ID счета',
        'full_name' => 'Имя',
        'pickup_contact.full_name' => 'Имя',
        'delivery_contact.full_name' => 'Имя',
        'shipper_contact.full_name' => 'Имя',
        'role_id' => 'Роль',
        'email' => 'Почта',
        'contact_email' => 'Почта',
        'recipient_email.*.value' => 'Почта',
        'state_id' => 'Штат',
        'pickup_contact.state_id' => 'Штат',
        'delivery_contact.state_id' => 'Штат',
        'shipper_contact.state_id' => 'Штат',
        'zip' => 'Индекс',
        'pickup_contact.zip' => 'Индекс',
        'delivery_contact.zip' => 'Индекс',
        'shipper_contact.zip' => 'Индекс',
        'city' => 'Город',
        'pickup_contact.city' => 'Город',
        'delivery_contact.city' => 'Город',
        'shipper_contact.city' => 'Город',
        'timezone' => 'Часовой пояс',
        'pickup_contact.timezone' => 'Часовой пояс',
        'delivery_contact.timezone' => 'Часовой пояс',
        'shipper_contact.timezone' => 'Часовой пояс',
        'type_id' => 'Тип',
        'pickup_contact.type_id' => 'Тип Контакта',
        'delivery_contact.type_id' => 'Тип Контакта',
        'shipper_contact.type_id' => 'Тип Контакта',
        'phones.*.number' => 'Телефон',
        'contact_phone' => 'Телефон',
        'contact_phones.*.number' => 'Телефон',
        'insurance_agent_phone' => 'Телефон агента',
        'insurance_deductible' => 'Подлежит вычету',
        'notification_emails.*.value' => 'Адреса для уведомлений',
        'receive_bol_copy_emails.*.value' => 'Адреса для получения копий',
        'insurance_agent_name' => 'Имя агента',
        'insurance_expiration_date' => 'Дата окончания срока',
        'working_hours' => 'График работы',
        'pickup_contact.working_hours' => 'График работы',
        'delivery_contact.working_hours' => 'График работы',
        'shipper_contact.working_hours' => 'График работы',
        'pickup_contact.address' => 'Адрес',
        'delivery_contact.address' => 'Адрес',
        'shipper_contact.address' => 'Адрес',
        'vehicles.*.make' => 'Марка',
        'vehicles.*.model' => 'Модель',
        'vehicles.*.type_id' => 'Тип',

        'payment.total_carrier_amount' => 'Общая сумма',
        'payment.customer_payment_amount' => 'Сумма',
        'payment.customer_payment_method_id' => 'Способ оплаты',
        'payment.customer_payment_location' => 'Место оплаты',
        'payment.broker_payment_amount' => 'Сумма',
        'payment.broker_payment_method_id' => 'Способ оплаты',
        'payment.broker_payment_days' => 'Количество дней',
        'payment.broker_payment_begins' => 'Условия начинаются',
        'payment.broker_fee_amount' => 'Сумма',
        'payment.broker_fee_method_id' => 'Способ оплаты',
        'payment.broker_fee_days' => 'Количество дней',
        'payment.broker_fee_begins' => 'Условия начинаются',

        'expenses.*.type_id' => 'Тип',
        'expenses.*.price' => 'Сумма',
        'expenses.*.date' => 'Дата',
        'comment' => 'Комментарий',
        'pickup_contact.phones.*.number' => 'Телефон',
        'delivery_contact.phones.*.number' => 'Телефон',
        'shipper_contact.phones.*.number' => 'Телефон',
        'pickup_contact.phone' => 'Телефон',
        'delivery_contact.phone' => 'Телефон',
        'shipper_contact.phone' => 'Телефон',

        'paid_method_id' => 'Способ оплаты',
        'reference_number' => 'Номер чека',
        'receipt_date' => 'Дата чека',
        'recipient_fax' => 'Факс',
        'send_via' => 'Отправить с помощью',

        'name' => 'Имя',
        'username' => 'Никнейм',
        //'email'                 => 'E-Mail адрес',
        'first_name' => 'Имя',
        'last_name' => 'Фамилия',
        'password' => 'Пароль',
        'password_confirmation' => 'Подтверждение пароля',
        'country' => 'Страна',
        'address' => 'Адрес',
        'phone' => 'Телефон',
        'mobile' => 'Моб. номер',
        'age' => 'Возраст',
        'sex' => 'Пол',
        'gender' => 'Пол',
        'day' => 'День',
        'month' => 'Месяц',
        'year' => 'Год',
        'hour' => 'Час',
        'minute' => 'Минута',
        'second' => 'Секунда',
        'title' => 'Наименование',
        'content' => 'Контент',
        'description' => 'Описание',
        'excerpt' => 'Выдержка',
        'date' => 'Дата',
        'time' => 'Время',
        'available' => 'Доступно',
        'size' => 'Размер',
        'usdot-not-exists' => 'Usdot не существует',

        'expenses_before.*.type' => 'Тип',
        'expenses_after.*.type' => 'Тип',
        'bonuses.*.type' => 'Тип',
        'expenses_before.*.price' => 'Сумма',
        'expenses_after.*.price' => 'Сумма',
        'bonuses.*.price' => 'Сумма',

        'customer_full_name' => 'ФИО клиента',

        'url' => 'URL',
        'contacts' => 'Контакты',
        'contacts.*.name' => 'Имя',
        'contacts.*.email' => 'Email',
        'contacts.*.phone' => 'Телефон',
        'contacts.*.phones.*.number' => 'Номер телефона',
        'contacts.*.phones.*.extension' => 'Онисание телефона',
        'contacts.*.position' => 'Позиция',
        'contacts.*.emails.*' => 'Emails',
        'contacts.*.emails.*.value' => 'Email',

        'color' => 'Цвет',
        'type' => 'Тип',

        'tags' => 'Теги',
        'tags.*' => 'Тег',

        'stock_number' => 'Сток номер',
        'quantity' => 'Количество',
        'price_wholesale' => 'Цена',
        'price_retail' => 'Цена',
        'category_id' => 'Категория',
        'supplier_id' => 'Поставщик',
        'notes' => 'Дополнительные заметки',
        'quantity_comment' => 'Причина',

        'vin' => 'VIN',
        'unit_number' => 'Юнит номер',
        'make' => 'Марка',
        'model' => 'Модель',
        'license_plate' => 'Номерной знак',
        'temporary_plate' => 'Временный номерной знак',

        'truck_id' => 'Трак',
        'trailer_id' => 'Трейлер',
        'discount' => 'Скидка',
        'tax_inventory' => 'Налог на детали',
        'tax_labor' => 'Налог на работу',
        'implementation_date' => 'Дата, время',
        'mechanic_id' => 'Механик',
        'types_of_work' => 'Типы работ',
        'types_of_work.*.name' => 'Имя',
        'types_of_work.*.save_to_the_list' => 'Сохранить тип работ в список',
        'types_of_work.*.duration' => 'Длительность',
        'types_of_work.*.hourly_rate' => 'Стоимость часа',
        'types_of_work.*.inventories' => 'Детали',
        'types_of_work.*.inventories.*.id' => 'Деталь',
        'types_of_work.*.inventories.*.quantity' => 'Количетсво',

        'accept_decimals' => 'Принимать десятичные',

        'unit_id' => 'Еденица измерения',

        'purchase.cost' => 'Цена',
        'purchase.quantity' => 'Количество',
        'purchase.invoice_number' => 'Номер инвойса',
        'purchase.date' => 'Дата',
        'invoice_number' => 'Номер инвойса',
        'price' => 'Цена',
        'cost' => 'Цена',
        'describe' => 'Причина',
        'due_date' => 'Дата окончания',
        'payment_date' => 'Дата оплаты',
        'payment_method' => 'Метод опалты',
        'company_name' => 'Наименование компании',
        'tax' => 'Налог',
        'driver_license.license_number' => 'Номер лицензии',
        'driver_license.issuing_state_id' => 'Штат',
        'driver_license.issuing_date' => 'Дата выдачи',
        'driver_license.expiration_date' => 'Дата истечения',
        'driver_license.category' => 'Категория',
        'driver_license.category_name' => 'Название категории',
        'driver_license.attached_document' => 'Документ',
        'previous_driver_license.license_number' => 'Номер лицензии',
        'previous_driver_license.issuing_state_id' => 'Штат',
        'previous_driver_license.issuing_date' => 'Дата выдачи',
        'previous_driver_license.expiration_date' => 'Дата истечения',
        'previous_driver_license.category' => 'Категория',
        'previous_driver_license.category_name' => 'Наименование категории',
        'previous_driver_license.attached_document' => 'Документ',
        'medical_card.card_number' => 'Номер карты',
        'medical_card.issuing_date' => 'Дата выдачи',
        'medical_card.expiration_date' => 'Дата истечения',
        'medical_card.medical_card_document' => 'Документ',
        'mvr.reported_date' => 'Дата',
        'company_info.name' => 'Название компании',
        'company_info.ein' => 'EIN',
        'company_info.address' => 'Адресс',
        'company_info.city' => 'Город',
        'company_info.zip' => 'Индекс',

        'company_id' => 'Компания',
        'imei' => 'IMEI',
    ],
];
