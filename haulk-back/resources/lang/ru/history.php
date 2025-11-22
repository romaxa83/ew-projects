<?php

return [


    'created' => 'Сознано :model',

    'updating' => 'Обновлено :model',

    'deleting' => 'Удалено :model',

    'restored' => 'Восстановлено :model',

    'models' => [
        'state' => 'Штат',
        'driver_info' => 'Информация о водителе'
    ],

    'send_bol_via_fax_success' => 'BOL был отправлен на номер: :number факсом',
    'send_invoice_via_fax_success' => 'Инвойс был отправлен на номер: :number факсом',
    'send_bol_via_fax_failed' => 'Отправка BOL на номер: :number факсом не удалась',
    'send_invoice_via_fax_failed' => 'Отправка Инвойс на номер: :number факсом не удалась',

    'add_bol_and_invoice_to_fax_queue' => 'Инвойс и Bol были добавлены :full_name на отправку по факсу на номер :number',
    'add_invoice_to_fax_queue' => 'Инвойс был добавлен :full_name на отправку по факсу на номер :number',
    'add_bol_to_fax_queue' => 'Bol был добавлен :full_name на отправку по факсу на номер :number',

    'sending_doc_to_fax' => ':document был добавлен пользователем :full_name для отправки по факсу на номер :number',
    'sending_docs_to_fax' => ':documents и :document были добавлены пользователем :full_name для отправки по факсу на номер :number',

    'delivered_doc_success' => ':document был доставлен на email :email',
    'delivered_doc_fail' => ':document не был доставлен на email :email',

    'delivered_docs_success' => ':documents и :document были доставлены на email :email',
    'delivered_docs_fail' => ':documents и :document не были доставлены на email :email',

    'sent_doc' => ':document был отправлен пользователем :full_name на email :emails',
    'sent_docs' => ':documents and :document были отправлены пользователем :full_name на email :emails',

    'sent_doc_auto' => ':document был отправлен автоматически на email :emails',
    'sent_docs_auto' => ':documents and :document были отправлены автоматически на email :emails',

    'send_bol_delivered' => 'BOL был доставлен на :email',
    'send_invoice_delivered' => 'Инвойс был доставлен на :email',
    'send_bol_and_invoice_delivered' => 'BOL и Инвойс были доставлены на :email',

    'send_bol_failed' => 'Доставка BOL на :email не удалась',
    'send_invoice_failed' => 'Доставка инвойса на :email не удалась',
    'send_bol_and_invoice_failed' => 'Доставка BOL и инвойса на :email не удалась',

    'bol_and_invoice_sent' => 'Инвойс и BOL были отправлены :full_name по электронной почте: :emails',
    'invoice_sent' => 'Инвойс был отправлен :full_name по электронной почте: :emails',
    'bol_sent' => 'BOL был отправлен :full_name по электронной почте: :emails',

    'order_created' => 'Заказ :load_id был создан',

    'driver_assigned_order' => 'Водитель :full_name назначен заказу :load_id',
    'driver_assigned_order_by' => 'Водитель :full_name назначен заказу :load_id пользователем :editor_name',

    'dispatcher_assigned_order_by' => 'Диспетчер :full_name назначен заказу :load_id пользователем :editor_name',

    'driver_removed_order' => 'Драйвер удален из заказа :load_id',
    'driver_removed_order_by' => 'Драйвер удален из заказа :load_id пользователем :editor_name',

    'dispatcher_removed_order_by' => 'Диспетчер удален из заказа :load_id пользователем :editor_name',

    'order_marked_paid' => 'Заказ :load_id был помечен как Оплачен :full_name',
    'order_marked_unpaid' => 'Заказ :load_id был возвращен на статус Выставлен Счет пользователем :full_name',
    'driver_received_payment' => 'Водитель :full_name получил деньги / чек на этапе :type',
    'driver_not_received_payment' => 'Водитель :full_name не получил денег / чек на этапе :type. Его комментарий ":comment"',
    'driver_attached_document' => 'Водитель :full_name прикрепил документ к заказу :load_id',
    'driver_deleted_document' => 'Водитель :full_name удалил документ из заказа :load_id',
    'driver_attached_photo' => 'Водитель :full_name прикрепил фото к заказу :load_id',
    'driver_deleted_photo' => 'Водитель :full_name удалил фото из заказа :load_id',
    'order_picked_up' => 'Водитель :full_name пометил заказ :load_id как picked up',
    'order_delivered' => 'Водитель :full_name пометил заказ :load_id как delivered',

    'offer_created' => 'Оффер :load_id был создан',
    'user_take_order' => 'Пользователь :full_name (:email) забрал заказ :load_id из офферов',
    'user_release_order' => 'Пользователь :full_name (:email) перевел заказ :load_id в офферы',
    'store_order_comment' => 'Пользователь :full_name с ролью :role и почтой :email оставил комментарий к заказу :load_id',
    'delete_order_comment' => 'Пользователь :full_name_1 удалил комментарий пользователя :full_name_2',

    'user_logged_in' => 'Пользователь :full_name (:email) вошел в систему',
    'admin_logged_in' => 'Администратор :full_name (:email) вошел в систему',

    'user_created' => ':role :full_name (:email) создал :changed_user_role',
    'user_updated' => ':role :full_name (:email) внес изменения в the :changed_user_role детали',
    'user_deleted' => ':role :full_name (:email) удалил :changed_user_role',
    'user_file_added' => ':role :full_name (:email) прикрепил файл',
    'user_file_deleted' => ':role :full_name (:email) удалил файл',
    'user_comment_created' => ':role :full_name (:email) оставил комментарий',
    'user_comment_deleted' => ':role :full_name (:email) удалил комментарий',
    'user_activated' => ':role :full_name (:email) активировал :changed_user_role',
    'user_deactivated' => ':role :full_name (:email) деактивировал :changed_user_role',

    'email_change_created' => 'Пользователь :full_name запросил смену почты :old_email => :new_email',
    'email_change_approved' => 'Пользователь :full_name (:email) одобрил смену почты :old_email => :new_email',
    'password_changed_by_admin' => 'Пользователь :admin_full_name (:admin_email) изменил пароль пользователю :user_full_name (:user_email)',
    'notification_settings_updated' => 'Пользователь :full_name (:email) обновил настройки уведомлений',
    'carrier_updated' => 'Пользователь :full_name (:email) обновил профиль компании',
    'carrier_info_photo_added' => 'Пользователь :full_name (:email) загрузил логотип компании',
    'carrier_info_photo_deleted' => 'Пользователь :full_name (:email) удалил логотип компании',
    'carrier_w9_photo_added' => 'Пользователь :full_name (:email) загрузил форму W9',
    'carrier_w9_photo_deleted' => 'Пользователь :full_name (:email) удалил форму W9',
    'carrier_usdot_photo_added' => 'Пользователь :full_name (:email) загрузил сертификат USDOT',
    'carrier_usdot_photo_deleted' => 'Пользователь :full_name (:email) удалил сертификат USDOT',
    'carrier_insurance_updated' => 'Пользователь :full_name (:email) обновил информацию о страховке',
    'carrier_insurance_photo_added' => 'Пользователь :full_name (:email) загрузил Certificate of insurance',
    'carrier_insurance_photo_deleted' => 'Пользователь :full_name (:email) удалил Certificate of insurance',
    'library_added_by_driver' => 'Водитель :full_name (:email) добавил документ в библиотеку',
    'contact_created' => 'Пользователь :full_name (:email) создал контакт :contact_full_name',
    'contact_updated' => 'Пользователь :full_name (:email) обновил контакт :contact_full_name',
    'contact_deleted' => 'Пользователь :full_name (:email) удалил контакт :contact_full_name',
    'news_created' => 'Пользователь :full_name (:email) создал новость ":news_title"',
    'news_updated' => 'Пользователь :full_name (:email) обновил новость ":news_title"',
    'news_deleted' => 'Пользователь :full_name (:email) удалил новость ":news_title"',
    'news_activated' => 'Пользователь :full_name (:email) активировал новость ":news_title"',
    'news_deactivated' => 'Пользователь :full_name (:email) деактивировал новость ":news_title"',
    'faq_created' => 'Пользователь :full_name (:email) создал вопрос ":question_en"',
    'faq_updated' => 'Пользователь :full_name (:email) обновил вопрос ":question_en"',
    'faq_deleted' => 'Пользователь :full_name (:email) удалил вопрос ":question_en"',
    'library_created' => 'Пользователь :full_name (:email) загрузил документ ":document"',
    'library_deleted' => 'Пользователь :full_name (:email) удалил документ ":document"',
    'order_deleted' => 'Пользователь :full_name (:email) удалил заказ :load_id',
    'order_restored' => 'Пользователь :full_name (:email) восстановил заказ :load_id',
    'order_deleted_permanently' => 'Пользователь :full_name (:email) удалил заказ :load_id полностью',
    'order_duplicated' => 'Заказ :new_load_id был дублирован из заказа :old_load_id',
    'order_changed' => ':role :full_name (:email) внес изменения в заказ',

    'order_add_vehicle' => 'Пользователь :full_name (:email) добавил автомобиль',
    'order_delete_vehicle' => 'Пользователь :full_name (:email) удалил автомобиль',
    'order_delete_expense' => 'Расход был удален',
    'order_delete_bonus' => 'Бонус был удален',
    'order_delete_attachment' => 'Файл был удален',

    'status_changed_manually' => 'Пользователь :full_name изменил статус заказа с :status_old на :status_new',

    'on_deduct_from_driver' => 'Пользователь :full_name (:email) установил отметку "Вычесть у водителя". Заказ :load_id',
    'on_deduct_from_driver_with_note' => 'Пользователь :full_name (:email) установил отметку "Вычесть у водителя". Заказ :load_id. Примечание: :note',
    'off_deduct_from_driver' => 'Пользователь :full_name (:email) убрал отметку "Вычесть у водителя". Заказ :load_id',

    'signed_inspection' => 'Пользователь :first_name :last_name (:email) подписал :location инспекцию.',

    'sent_signature_link' => 'Пользователь :full_name (:email_sender) отправил ссылку для подписи :location инспекции на email :email_recipient',

    'delivered_signature_link_success' => 'Ссылка для подписи :location инспекции была доставлена на email :email_recipient',
    'delivered_signature_link_fail' => 'Ссылка для подписи :location инспекции не была доставлена на email :email_recipient',

    'payment_stage_added' => 'Пользователь :full_name (:email) добавил оплату в заказ',
    'payment_stage_deleted' => 'Пользователь :full_name (:email) удалил платеж из заказа',

    'bs' => [
        'inventory_created' => 'Инвентарь :stock_number создан',
        'inventory_changed' => ':role :full_name (:email) внес изменения в инвентарь',
        'inventory_quantity_reserved_for_order' => ':role :full_name (:email) зарезервировал инвентарь :inventory_name, :stock_number по цене :price для заказа <a href=":order_link">:order_number</a>',
        'inventory_quantity_reserved_additionally_for_order' => ':role :full_name (:email) зарезервировал дополнительное количество инвентаря :inventory_name, :stock_number по цене :price для заказа <a href=":order_link">:order_number</a>',
        'inventory_quantity_reduced_from_order' => ':role :full_name (:email) отменил резервирование инвентаря :inventory_name, :stock_number по цене :price для заказа <a href=":order_link">:order_number</a>',
        'inventory_price_changed_for_order' => ':role :full_name (:email) зарезервировал инвентарь :inventory_name, :stock_number по цене :price для заказа <a href=":order_link">:order_number</a>',
        'finished_order_with_inventory' => ':role :full_name (:email) завершил заказ <a href=":order_link" target="_blank">:order_number</a> используя инвентарь :inventory_name, :stock_number по цене :price',
        'inventory_quantity_increased' => ':role :full_name (:email) увеличил количество инвентаря :inventory_name, :stock_number',
        'inventory_quantity_decreased' => ':role :full_name (:email) уменьшил количество инвентаря :inventory_name, :stock_number',
        'inventory_quantity_decreased_sold' => ':role :full_name (:email) уменьшил количество инвентаря :inventory_name, :stock_number. Счет для :inventory_name, :stock_number был оплачен.',
        'inventory_quantity_returned_for_deleted_order' => ':role :full_name (:email) вурнул инвентарь :inventory_name, :stock_number для удаленного заказа :order_number',

        'BodyShopAdmin' => 'Администратор',
        'BodyShopSuperAdmin' => 'Супер Администратор',

        'order_created' => 'Заказ :order_number создан',
        'order_changed' => ':role :full_name (:email) внез изменения в заказ',
        'order_delete_attachment' => 'Приложение было удалено',
        'attached_document' => ':full_name добавил файл в заказ',
        'store_order_comment' => 'Пользователь :full_name с ролью :role и email :email оставил комментарий к заказу :order_number',
        'delete_order_comment' => 'Пользователь :full_name_1 удалил комментарий пользователя :full_name_2',
        'status_changed' => ':role :full_name (:email) отметил заказ как :status',
        'order_reassigned_mechanic' => 'Механик :mechanic_name привязан к заказу :order_number :role :full_name',
        'order_deleted' => ':role :full_name (:email) удалил заказ :order_number',
        'order_restored' => ':role :full_name (:email) восстановил заказ :order_number',
        'order_send_docs' => 'Инвойс отправлен :role :full_name (:email) к :receivers',
        'order_created_payment' => ':role :full_name (:email) добавил платеж к заказу :order_number',
        'order_deleted_payment' => ':role :full_name (:email) удалил платеж из заказа :order_number',

        'order_status' => [
            'new' => 'Новый',
            'finished' => 'Завершенный',
            'in_process' => 'В процессе',
            'deleted' => 'Удаленный',
        ],
    ],
];
