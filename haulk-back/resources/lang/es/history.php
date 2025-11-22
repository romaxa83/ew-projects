<?php

return [


    'created' => 'Creada :model',

    'updating' => 'Actualización :model',

    'deleting' => 'Borrando :model',

    'restored' => 'Restaurada :model',

    'models' => [
        'state' => 'Estado',
        'driver_info' => 'Información del conductor'
    ],

    'send_bol_via_fax_success' => 'BOL was sent to fax :number',
    'send_invoice_via_fax_success' => 'Invoice was sent to fax :number',
    'send_bol_via_fax_failed' => 'Delivery of the BOL by fax to :number failed',
    'send_invoice_via_fax_failed' => 'Delivery of the Invoice by fax to :number failed',

    'add_bol_and_invoice_to_fax_queue' => 'Preparing by :full_name to send invoice and Bol by fax to :number',
    'add_invoice_to_fax_queue' => 'Preparing by :full_name to send invoice by fax to :number',
    'add_bol_to_fax_queue' => 'Preparing by :full_name to send BOL by fax to :number',

    'sending_doc_to_fax' => ':full_name agregó :document para enviar por fax al :number',
    'sending_docs_to_fax' => ':full_name agregó :documents y :document para enviar por fax al :number',

    'delivered_doc_success' => ':document fue entregada a :email',
    'delivered_doc_fail' => ':document no fue entregada a :email',

    'delivered_docs_success' => ':documents y la :document se enviaron a :email',
    'delivered_docs_fail' => ':documents y la :document no se enviaron a :email',

    'sent_doc' => ':full_name agregó :document fue enviado a :emails',
    'sent_docs' => ':full_name agregó :documents y la :document se enviaron a :emails',

    'sent_doc_auto' => ':document se envió automáticamente a :emails',
    'sent_docs_auto' => ':documents y la :document se envió automáticamente a :emails',

    'send_bol_delivered' => 'BOL del pedido :load_id se entregó a :email',
    'send_invoice_delivered' => 'Factura del pedido :load_id fue entregado a :email',
    'send_bol_and_invoice_delivered' => 'BOL y la factura del pedido :load_id fue entregado a :email',

    'send_bol_failed' => 'Entrega del BOL del pedido :load_id a :email fallido',
    'send_invoice_failed' => 'Entrega de la factura del pedido :load_id a :email fallido',
    'send_bol_and_invoice_failed' => 'Entrega de la BOL y la factura del pedido :load_id a :email fallido',

    'bol_and_invoice_sent' => 'Invoice and Bol was emailed by :full_name to :emails',
    'invoice_sent' => 'Factura por el pedido :load_id fue enviado por :full_name (:emails)',
    'bol_sent' => 'BOL del pedido :load_id fue enviado por :full_name (:emails)',

    'order_created' => 'Orden :load_id fue creada',

    'driver_assigned_order' => 'Controlador :full_name asignado a la orden :load_id',
    'driver_assigned_order_by' => 'Controlador :full_name asignado a la orden :load_id por :editor_name',

    'dispatcher_assigned_order_by' => 'Despachador :full_name asignado a la orden :load_id por :editor_name',

    'driver_removed_order' => 'Controlador eliminado del pedido :load_id',
    'driver_removed_order_by' => 'Controlador eliminado del pedido :load_id por :editor_name',

    'dispatcher_removed_order_by' => 'Despachador eliminado del pedido :load_id por :editor_name',

    'order_marked_paid' => 'Orden :load_id fue marcado como Pagado por :full_name',
    'order_marked_unpaid' => 'Order :load_id fue devuelto al estado Facturado por el usuario :full_name',
    'driver_received_payment' => 'Conductor :full_name recibió dinero / cheque en :type',
    'driver_not_received_payment' => 'Driver :full_name no recibió dinero / cheque en :type. Su comentario ":comment"',
    'driver_attached_document' => 'Controlador :full_name adjuntó un documento al pedido :load_id',
    'driver_deleted_document' => 'Controlador :full_name borró un documento del pedido :load_id',
    'driver_attached_photo' => 'Controlador :full_name adjuntó una foto al pedido :load_id',
    'driver_deleted_photo' => 'Controlador :full_name borró una foto del pedido :load_id',
    'order_picked_up' => 'Controlador :full_name marcó el pedido :load_id como recogido desde el origen',
    'order_delivered' => 'Controlador :full_name marcó el pedido :load_id como entregado al destino',

    'offer_created' => 'Oferta: se creó load_id',
    'user_take_order' => 'Usuario :full_name (:email) tomó el pedido :load_id de las ofertas',
    'user_release_order' => 'Usuario :full_name (:email) movió el pedido :load_id a ofertas',
    'store_order_comment' => 'Usuario :full_name con role :role y email :email ha dejado un comentario para el pedido :load_id',
    'delete_order_comment' => 'Usuario :full_name_1 ha eliminado el comentario del usuario :full_name_2',

    'user_logged_in' => 'Usuario :full_name (:email) ha iniciado sesión correctamente',
    'admin_logged_in' => 'Administración :full_name (:email) ha iniciado sesión correctamente',

    'user_created' => 'Usuario :full_name con role :role y correo electrónico :email se creó el correo electrónico',
    'user_updated' => 'Usuario :full_name con role :role y correo electrónico :email se actualizó el correo electrónico',
    'user_deleted' => 'Usuario :full_name con role :role y correo electrónico :email se eliminó el correo electrónico',
    'user_file_added' => ':role :full_name (:email) attached a file',
    'user_file_deleted' => ':role :full_name (:email) removed a file',
    'user_comment_created' => ':role :full_name (:email) has left a comment',
    'user_comment_deleted' => ':role :full_name (:email) has deleted a comment',
    'user_activated' => ':role :full_name (:email) has activated the :changed_user_role',
    'user_deactivated' => ':role :full_name (:email) has deactivated the :changed_user_role',

    'email_change_created' => 'Usuario :full_name solicitó un cambio de correo electrónico :old_email => :new_email',
    'email_change_approved' => 'Usuario :full_name (:email) cambio de correo electrónico aprobado :old_email => :new_email',
    'password_changed_by_admin' => 'Usuario :admin_full_name (:admin_email) cambió la contraseña del usuario :user_full_name (:user_email)',
    'notify_settings_updated' => 'Usuario :full_name (:email) ha actualizado la configuración de notificaciones',
    'carrier_updated' => 'Usuario :full_name (:email) ha actualizado el perfil de la compañía',
    'carrier_info_photo_added' => 'Usuario :full_name (:email) ha agregado el logotipo de la compañía',
    'carrier_info_photo_deleted' => 'Usuario :full_name (:email) ha eliminado el logotipo de la empresa',
    'carrier_w9_photo_added' => 'Usuario :full_name (:email) ha agregado el formulario W9',
    'carrier_w9_photo_deleted' => 'Usuario :full_name (:email) ha eliminado el formulario W9',
    'carrier_usdot_photo_added' => 'Usuario :full_name (:email) ha agregado el Certificado USDOT',
    'carrier_usdot_photo_deleted' => 'Usuario :full_name (:email) ha eliminado el Certificado USDOT',
    'carrier_insurance_updated' => 'Usuario :full_name (:email) ha actualizado la información del seguro de la compañía',
    'carrier_insurance_photo_added' => 'Usuario :full_name (:email) ha agregado un Certificado de seguro',
    'carrier_insurance_photo_deleted' => 'Usuario :full_name (:email) ha eliminado el Certificado de seguro',
    'library_added_by_driver' => 'Driver :full_name (:email) ha agregado un nuevo documento a la biblioteca',
    'contact_created' => 'Usuario :full_name (:email) ha creado el contacto :contact_full_name',
    'contact_updated' => 'Usuario :full_name (:email) ha actualizado el contacto :contact_full_name',
    'contact_deleted' => 'Usuario :full_name (:email) ha eliminado el contacto :contact_full_name',
    'news_created' => 'Usuario :full_name (:email) ha creado un registro de noticias ":news_title"',
    'news_updated' => 'Usuario :full_name (:email) ha actualizado un registro de noticias ":news_title"',
    'news_deleted' => 'Usuario :full_name (:email) ha eliminado un registro de noticias ":news_title"',
    'news_activated' => 'Usuario :full_name (:email) ha activado un registro de noticias ":news_title"',
    'news_deactivated' => 'Usuario :full_name (:email) ha desactivado un registro de noticias ":news_title"',
    'faq_created' => 'Usuario :full_name (:email) ha creado una pregunta ":question_en"',
    'faq_updated' => 'Usuario :full_name (:email) ha actualizado una pregunta ":question_en"',
    'faq_deleted' => 'Usuario :full_name (:email) ha eliminado una pregunta ":question_en"',
    'library_created' => 'Usuario :full_name (:email) ha agregado un documento ":document"',
    'library_deleted' => 'Usuario :full_name (:email) ha eliminado un documento ":document"',
    'order_deleted' => 'Usuario :full_name (:email) borró el pedido :load_id',
    'order_restored' => 'Usuario :full_name (:email) restauró el pedido :load_id',
    'order_deleted_permanently' => 'Usuario :full_name (:email) eliminó el pedido :load_id permanentemente',
    'order_duplicated' => 'Pedido :new_load_id fue duplicado del pedido :old_load_id',
    'order_changed' => ':role :full_name (:email) realizó cambios en el pedido',

    'order_add_vehicle' => 'El usuario :full_name (:email) ha agregado un vehículo',
    'order_delete_vehicle' => 'El usuario :full_name (:email) ha eliminado el vehículo',
    'order_delete_expense' => 'Se eliminó el gasto',
    'order_delete_bonus' => 'Se eliminó la bonificación',
    'order_delete_attachment' => 'Se eliminó el archivo adjunto',

    'status_changed_manually' => 'Usuario :full_name ha cambiado el estado del pedido de :status_old a :status_new',

    'on_deduct_from_driver' => 'Usuario :full_name (:email) estableció el indicador "Deducir del controlador". Pedido :load_id',
    'on_deduct_from_driver_with_note' => 'Usuario :full_name (:email) estableció el indicador "Deducir del controlador". Pedido :load_id. Nota: :note',
    'off_deduct_from_driver' => 'Usuario :full_name (:email) indicador desarmado "Deducir del controlador". Pedido :load_id',

    'signed_inspection' => 'El usuario :first_name :last_name (:email) firmó la inspección de :location.',

    'sent_signature_link' => 'el enlace para firmar la inspección de :location fue enviado por el usuario :full_name (:email_sender) al correo electrónico :email_recipient',

    'delivered_signature_link_success' => 'El enlace para la firma de la inspección de :location se envió a :email_recipient',
    'delivered_signature_link_fail' => 'El enlace para la firma de la inspección de :location no se envió a :email_recipient',

    'payment_stage_added' => 'Usuario :full_name (:email) pago agregado al pedido',
    'payment_stage_deleted' => 'Usuario :full_name (:email) pago eliminado del pedido',
    'bs' => [
        'inventory_created' => 'Inventory :stock_number was created',
        'inventory_changed' => ':role :full_name (:email) made changes to the inventory',
        'inventory_quantity_reserved_for_order' => ':role :full_name (:email) reserved inventory :inventory_name, :stock_number at a price of :price for the order <a href=":order_link">:order_number</a>',
        'inventory_quantity_reserved_additionally_for_order' => ':role :full_name (:email) reserved additional quantity of inventory :inventory_name, :stock_number at a price of :price for the order <a href=":order_link">:order_number</a>',
        'inventory_quantity_reduced_from_order' => ':role :full_name (:email) reduced reserved quantity of inventory :inventory_name, :stock_number at a price of :price for the order <a href=":order_link">:order_number</a>',
        'inventory_price_changed_for_order' => ':role :full_name (:email) reserved quantity of inventory :inventory_name, :stock_number at a price of :price for the order <a href=":order_link">:order_number</a>',
        'finished_order_with_inventory' => ':role :full_name (:email) finished order <a href=":order_link" target="_blank">:order_number</a> using inventory :inventory_name, :stock_number at a price of :price',
        'inventory_quantity_increased' => ':role :full_name (:email) increased quantity of inventory :inventory_name, :stock_number',
        'inventory_quantity_decreased' => ':role :full_name (:email) decreased quantity of inventory :inventory_name, :stock_number',
        'inventory_quantity_decreased_sold' => ':role :full_name (:email) decreased quantity of inventory :inventory_name, :stock_number. Invoice for :inventory_name, :stock_number was paid.',
        'inventory_quantity_returned_for_deleted_order' => ':role :full_name (:email) returned inventory :inventory_name, :stock_number for the deleted order :order_number',

        'BodyShopAdmin' => 'Admin',
        'BodyShopSuperAdmin' => 'Super Admin',

        'order_created' => 'Order :order_number was created',
        'order_changed' => ':role :full_name (:email) made changes to the order',
        'order_delete_attachment' => 'Attachment was deleted',
        'attached_document' => ':full_name attached a file to the order',
        'store_order_comment' => 'User :full_name with role :role and email :email has left a comment for the order :order_number',
        'delete_order_comment' => 'User :full_name_1 has deleted the comment of the user :full_name_2',
        'status_changed' => ':role :full_name (:email) marked the order as :status',
        'order_reassigned_mechanic' => 'Mechanic :mechanic_name is assigned to order :order_number by :role :full_name',
        'order_deleted' => ':role :full_name (:email) deleted the order :order_number',
        'order_restored' => ':role :full_name (:email) restored the order :order_number',
        'order_send_docs' => 'Invoice was sent by :role :full_name (:email) to :receivers',
        'order_created_payment' => ':role :full_name (:email) added payment to the order :order_number',
        'order_deleted_payment' => ':role :full_name (:email) deleted payment from the order :order_number',

        'order_status' => [
            'new' => 'New',
            'finished' => 'Finished',
            'in_process' => 'In process',
            'deleted' => 'Deleted',
        ],
    ],
];
