<?php

return [


    'created' => 'Створено :model',

    'updating' => 'Оновлено :model',

    'deleting' => 'Видалено :model',

    'restored' => 'Відновлено :model',

    'models' => [
        'state' => 'Штат',
        'driver_info' => 'Інформація про водія'
    ],

    'send_bol_via_fax_success' => 'BOL був відправлений на номер: :number факсом',
    'send_invoice_via_fax_success' => 'Рахунок був відправлений на номер: :number факсом',
    'send_bol_via_fax_failed' => 'Відправка BOL на номер: :number факсом не вдалась',
    'send_invoice_via_fax_failed' => 'Відправка рахунку на номер: :number факсом не вдалась',

    'add_bol_and_invoice_to_fax_queue' => 'Рахунок і Bol були додані :full_name на відправку факсом на номер :number',
    'add_invoice_to_fax_queue' => 'Рахунок був доданий :full_name на відправку факсом на номер :number',
    'add_bol_to_fax_queue' => 'Bol був доданий :full_name на відправку факсом на номер :number',

    'sending_doc_to_fax' => ':document був доданий користувачем :full_name для відправки факсом на номер :number',
    'sending_docs_to_fax' => ':documents та :document були додані користувачем :full_name для відправки факсом на номер :number',

    'delivered_doc_success' => ':document був доставлений на email :email',
    'delivered_doc_fail' => ':document не був доставлений на email :email',

    'delivered_docs_success' => ':documents та :document були доставлені на email :email',
    'delivered_docs_fail' => ':documents та :document не були доставлені на email :email',

    'sent_doc' => ':document був надісланий користувачем :full_name на email :emails',
    'sent_docs' => ':documents and :document були надіслані користувачем :full_name на email :emails',

    'send_bol_delivered' => 'BOL був доставлений на :email',
    'send_invoice_delivered' => 'Рахунок був доставлений на :email',
    'send_bol_and_invoice_delivered' => 'BOL та рахунок були доставлені на :email',

    'send_bol_failed' => 'Доставка BOL на :email не вдалася',
    'send_invoice_failed' => 'Доставка рахунку на :email не вдалася',
    'send_bol_and_invoice_failed' => 'Доставка BOL та рахунку на :email не вдалася',

    'bol_and_invoice_sent' => 'Рахунок і BOL були відправлені :full_name електронною поштою: :emails',
    'invoice_sent' => 'Рахунок був відправлений :full_name електронною поштою: :emails',
    'bol_sent' => 'BOL надіслав :full_name електронною поштою: :emails',

    'order_created' => 'Замовлення :load_id було створено',

    'driver_assigned_order' => 'Водій :full_name призначений для замовлення :load_id',
    'driver_assigned_order_by' => 'Водій :full_name був призначений на замовлення :load_id користувачем :editor_name',

    'dispatcher_assigned_order_by' => 'Диспетчер :full_name був призначений на замовлення :load_id користувачем :editor_name',

    'driver_removed_order' => 'Водія було видалено із замовлення :load_id',
    'driver_removed_order_by' => 'Водія було видалено із замовлення :load_id користувачем :editor_name',

    'dispatcher_removed_order_by' => 'Диспетчера було видалено із замовлення :load_id користувачем :editor_name',

    'order_marked_paid' => 'Замовлення :load_id було позначено як Оплачено :full_name',
    'order_marked_unpaid' => 'Замовлення :load_id було повернено на статус Billed користувачем :full_name',
    'driver_received_payment' => 'Водій :full_name отримав гроші / чек на етапі :type',
    'driver_not_received_payment' => 'Водій :full_name не отримав грошей / чек на етапі :type. Його коментар ":comment"',
    'driver_attached_document' => 'Водій :full_name прикріпив документ до замовлення :load_id',
    'driver_deleted_document' => 'Водій :full_name видалив документ із замовлення :load_id',
    'driver_attached_photo' => 'Водій :full_name прикріпив фото до замовлення :load_id',
    'driver_deleted_photo' => 'Водій :full_name видалив фото із замовлення :load_id',
    'order_picked_up' => 'Водій :full_name помітив замовлення :load_id як picked up',
    'order_delivered' => 'Водій :full_name помітив замовлення :load_id як delivered',

    'offer_created' => 'Замовлення :load_id було створене',
    'user_take_order' => 'Користувач :full_name (:email) забрав замовлення :load_id з офферів',
    'user_release_order' => 'Користувач :full_name (:email) відправив замовлення :load_id в оффери',
    'store_order_comment' => 'Користувач :full_name під роллю :role та поштою :email залишив коментар до замовлення :load_id',
    'delete_order_comment' => 'Користувач :full_name_1 видалив коментар користувача :full_name_2',

    'user_logged_in' => 'Користувач :full_name (:email) увійшов до системи',
    'admin_logged_in' => 'Адміністратор :full_name (:email) увійшов до системи',

    'user_created' => ':role :full_name (:email) створив :changed_user_role',
    'user_updated' => ':role :full_name (:email) вніс зміни до :changed_user_role details',
    'user_deleted' => ':role :full_name (:email) видалив :changed_user_role',
    'user_file_added' => ':role :full_name (:email) додав файл',
    'user_file_deleted' => ':role :full_name (:email) видалив файл',
    'user_comment_created' => ':role :full_name (:email) залишив коментар',
    'user_comment_deleted' => ':role :full_name (:email) видалив коментар',
    'user_activated' => ':role :full_name (:email) активував :changed_user_role',
    'user_deactivated' => ':role :full_name (:email) деактивував :changed_user_role',

    'email_change_created' => 'Користувач :full_name запросив зміну пошти :old_email => :new_email',
    'email_change_approved' => 'Користувач :full_name (:email) схвалив зміну пошти :old_email => :new_email',
    'password_changed_by_admin' => 'Користувач :admin_full_name (:admin_email) змінив пароль користувачеві :user_full_name (:user_email)',
    'notification_settings_updated' => 'Користувач :full_name (:email) оновив налаштування повідомлень',
    'carrier_updated' => 'Користувач :full_name (:email) оновив профіль компанії',
    'carrier_info_photo_added' => 'Користувач :full_name (:email) завантажив логотип компанії',
    'carrier_info_photo_deleted' => 'Користувач :full_name (:email) видалив логотип компанії',
    'carrier_w9_photo_added' => 'Користувач :full_name (:email) завантажив форму W9',
    'carrier_w9_photo_deleted' => 'Користувач :full_name (:email) видалив форму W9',
    'carrier_usdot_photo_added' => 'Користувач :full_name (:email) завантажив сертифікат USDOT',
    'carrier_usdot_photo_deleted' => 'Користувач :full_name (:email) видалив сертифікат USDOT',
    'carrier_insurance_updated' => 'Користувач :full_name (:email) оновив інформацію про страховку',
    'carrier_insurance_photo_added' => 'Користувач :full_name (:email) завантажив Certificate of insurance',
    'carrier_insurance_photo_deleted' => 'Користувач :full_name (:email) видалив Certificate of insurance',
    'library_added_by_driver' => 'Водій :full_name (:email) додав документ до бібліотеки',
    'contact_created' => 'Користувач :full_name (:email) створив контакт :contact_full_name',
    'contact_updated' => 'Користувач :full_name (:email) оновив контакт :contact_full_name',
    'contact_deleted' => 'Користувач :full_name (:email) видалив контакт :contact_full_name',
    'news_created' => 'Користувач :full_name (:email) створив новину ":news_title"',
    'news_updated' => 'Користувач :full_name (:email) оновив новину ":news_title"',
    'news_deleted' => 'Користувач :full_name (:email) видалив новину ":news_title"',
    'news_activated' => 'Користувач :full_name (:email) активував новину ":news_title"',
    'news_deactivated' => 'Користувач :full_name (:email) деактивував новину ":news_title"',
    'faq_created' => 'Користувач :full_name (:email) створив питання ":question_en"',
    'faq_updated' => 'Користувач :full_name (:email) оновив питання ":question_en"',
    'faq_deleted' => 'Користувач :full_name (:email) видалив питання ":question_en"',
    'library_created' => 'Користувач :full_name (:email) завантажив документ ":document"',
    'library_deleted' => 'Користувач :full_name (:email) видалив документ ":document"',
    'order_deleted' => 'Користувач :full_name (:email) видалив замовлення :load_id',
    'order_restored' => 'Користувач :full_name (:email) відновив замовлення :load_id',
    'order_deleted_permanently' => 'Користувач :full_name (:email) видалив замовлення :load_id повністю',
    'order_duplicated' => 'Замовлення :new_load_id було дубльовано із замовлення :old_load_id',
    'order_changed' => ':role :full_name (:email) вніс зміни до замовлення',

    'order_delete_vehicle' => 'Автомобіль був видалений',
    'order_delete_expense' => 'Витрати були видалені',
    'order_delete_bonus' => 'Бонус було видалено',
    'order_delete_attachment' => 'Файл видалено',

    'status_changed_manually' => 'Користувач :full_name змінив статус замовлення з :status_old на :status_new',

    'on_deduct_from_driver' => 'Користувач :full_name (:email) встановив позначку "deduct from driver". Замовлення :load_id',
    'on_deduct_from_driver_with_note' => 'Користувач :full_name (:email) встановив позначку "deduct from driver". Замовлення: load_id. Примітка: :note',
    'off_deduct_from_driver' => 'Користувач :full_name (:email) прибрав позначку "deduct from driver". Замовлення :load_id',

    'signed_inspection' => 'Користувач :first_name :last_name (:email) встановив :location інспекцію.',

    'sent_signature_link' => 'Користувач :full_name (:email_sender) надіслав посилання для підпису :location інспекції на email :email_recipient',

    'delivered_signature_link_success' => 'Посилання для підпису :location перевірка була доставлена :email_recipient',
    'delivered_signature_link_fail' => 'Посилання для підпису :location перевірка не була доставлена :email_recipient',

    'payment_stage_added' => 'Користувач :full_name (:email) додав оплату до замовлення',
    'payment_stage_deleted' => 'Користувач :full_name (:email) видалив оплату із замовлення',

    'bs' => [
        'inventory_created' => 'Інвентар :stock_number створений',
        'inventory_changed' => ':role :full_name (:email) вніс зміни до інвентаря',
        'inventory_quantity_reserved_for_order' => ':role :full_name (:email) зарезервував інвентар :inventory_name, :stock_number по ціні :price для замовлення <a href=":order_link">:order_number</a>',
        'inventory_quantity_reserved_additionally_for_order' => ':role :full_name (:email) зарезервував додатковий інвентар :inventory_name, :stock_number по ціні :price для замовлення <a href=":order_link">:order_number</a>',
        'inventory_quantity_reduced_from_order' => ':role :full_name (:email) повернув зарезервований інвентар :inventory_name, :stock_number по ціні :price для замовлення <a href=":order_link">:order_number</a>',
        'inventory_price_changed_for_order' => ':role :full_name (:email) зарезервував інвентар :inventory_name, :stock_number по ціні :price для замовлення <a href=":order_link">:order_number</a>',
        'finished_order_with_inventory' => ':role :full_name (:email) finished order <a href=":order_link" target="_blank">:order_number</a> з використанням інвентаря :inventory_name, :stock_number по ціні :price',
        'inventory_quantity_increased' => ':role :full_name (:email) збільшив кількість інвентаря :inventory_name, :stock_number',
        'inventory_quantity_decreased' => ':role :full_name (:email) зменшив кількість інвентаря :inventory_name, :stock_number',
        'inventory_quantity_decreased_sold' => ':role :full_name (:email) зменшив кількість інвентаря :inventory_name, :stock_number. Рахунок для :inventory_name, :stock_number був сплаченй.',
        'inventory_quantity_returned_for_deleted_order' => ':role :full_name (:email) повернув інвентар :inventory_name, :stock_number для видаленого замовлення :order_number',

        'BodyShopAdmin' => 'Адміністратор',
        'BodyShopSuperAdmin' => 'Супер Адміністратор',

        'order_created' => 'Order :order_number створений',
        'order_changed' => ':role :full_name (:email) вніс зміни до замовлення',
        'order_delete_attachment' => 'Додаток був видалений',
        'attached_document' => ':full_name додав файл до замовлення',
        'store_order_comment' => 'Користувач :full_name з ролью :role і email :email залишив коментар до замовлення :order_number',
        'delete_order_comment' => 'Користувач :full_name_1 видалив коментар користувача :full_name_2',
        'status_changed' => ':role :full_name (:email) помітив замовлення як :status',
        'order_reassigned_mechanic' => 'Механік :mechanic_name призначений до замовлення :order_number :role :full_name',
        'order_deleted' => ':role :full_name (:email) видалив замовлення :order_number',
        'order_restored' => ':role :full_name (:email) відновив замовлення :order_number',
        'order_send_docs' => 'Інвойс відправлений :role :full_name (:email) до :receivers',
        'order_created_payment' => ':role :full_name (:email) додав платіж до замовлення :order_number',
        'order_deleted_payment' => ':role :full_name (:email) видалив платіж до замовлення :order_number',

        'order_status' => [
            'new' => 'Новий',
            'finished' => 'Завершений',
            'in_process' => 'У процесі',
            'deleted' => 'Видалений',
        ],
    ],
];
