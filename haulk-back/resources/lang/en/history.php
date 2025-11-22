<?php

return [


    'created' => 'Created :model',

    'updating' => 'Updating :model',

    'deleting' => 'Deleting :model',

    'restored' => 'Restored :model',

    'models' => [
        'state' => 'State',
        'driver_info' => 'Driver info'
    ],

    /**Deprecated**/
    'send_bol_via_fax_success' => 'BOL was sent to fax :number',
    'send_invoice_via_fax_success' => 'Invoice was sent to fax :number',
    'send_w9_via_fax_success' => 'W9 certificate was sent to fax :number',
    'send_bol_via_fax_failed' => 'BOL was not faxed to :number. Please retry or try with another fax number',
    'send_invoice_via_fax_failed' => 'Invoice was not faxed to :number. Please retry or try with another fax number',
    'send_w9_via_fax_failed' => 'W9 certificate was not faxed to :number. Please retry or try with another fax number',

    'add_bol_and_invoice_to_fax_queue' => 'Preparing by :full_name to send invoice and Bol by fax to :number',
    'add_invoice_to_fax_queue' => 'Preparing by :full_name to send invoice by fax to :number',
    'add_bol_to_fax_queue' => 'Preparing by :full_name to send BOL by fax to :number',

    'send_bol_delivered' => 'BOL was delivered to :email',
    'send_invoice_delivered' => 'Invoice was delivered to :email',
    'send_bol_and_invoice_delivered' => 'BOL and Invoice was delivered to :email',

    'send_bol_failed' => 'Delivery of the BOL to :email failed',
    'send_invoice_failed' => 'Delivery of the Invoice to :email failed',
    'send_bol_and_invoice_failed' => 'Delivery of the BOL and the Invoice  to :email failed',

    'bol_and_invoice_sent' => 'Invoice and Bol was emailed by :full_name to :emails',
    'invoice_sent' => 'Invoice was emailed by :full_name to :emails',
    'bol_sent' => 'BOL was emailed by :full_name to :emails',
    /*************/

    'sending_doc_to_fax' => ':document was added by :full_name for sending by fax to :number',
    'sending_docs_to_fax' => ':documents and :document were added by :full_name for sending by fax to :number',

    'delivered_doc_success' => ':document was delivered to :email',
    'delivered_doc_fail' => ':document was not delivered to :email',

    'delivered_docs_success' => ':documents and :document were delivered to :email',
    'delivered_docs_fail' => ':documents and :document were not delivered to :email',

    'sent_doc' => ':document was sent by :full_name to :emails',
    'sent_docs' => ':documents and :document were sent by :full_name to :emails',

    'sent_doc_auto' => ':document was sent automatically to :emails',
    'sent_docs_auto' => ':documents and :document were sent automatically to :emails',

    'send_invoice_broker_failed' => 'Delivery of the broker invoice failed.:message',
    'send_invoice_customer_failed' => 'Delivery of the customer invoice failed.:message',

    'order_created' => 'Order :load_id was created',

    'driver_assigned_order' => 'Driver :full_name assigned to order :load_id',
    'driver_assigned_order_by' => 'Driver :full_name assigned to order :load_id by :editor_name',

    'dispatcher_assigned_order_by' => 'Dispatcher :full_name assigned to order :load_id by :editor_name',

    'driver_removed_order' => 'Driver removed from order :load_id',
    'driver_removed_order_by' => 'Driver removed from order :load_id by :editor_name',

    'dispatcher_removed_order_by' => 'Dispatcher removed from order :load_id by :editor_name',

    'order_marked_paid' => 'Order :load_id was marked as Paid by :full_name',
    'order_marked_unpaid' => 'Order :load_id was reverted to Billed by :full_name',
    'driver_received_payment' => 'Driver :full_name received money/check on :type',
    'driver_not_received_payment' => 'Driver :full_name did\'t received money/check on :type. His comment ":comment"',
    'driver_attached_document' => 'Driver :full_name attached a document to the order :load_id',
    'driver_deleted_document' => 'Driver :full_name deleted a document from the order :load_id',
    'driver_attached_photo' => 'Driver :full_name attached a photo to the order :load_id',
    'driver_deleted_photo' => 'Driver :full_name deleted a photo from the order :load_id',
    'order_picked_up' => 'Driver :full_name marked the order :load_id as picked up from origin',
    'order_delivered' => 'Driver :full_name marked the order :load_id as delivered to destination',

    'offer_created' => 'Offer :load_id was created',
    'user_take_order' => 'User :full_name (:email) took the order :load_id from offers',
    'user_release_order' => 'User :full_name (:email) moved the order :load_id to offers',
    'store_order_comment' => 'User :full_name with role :role and email :email has left a comment for the order :load_id',
    'delete_order_comment' => 'User :full_name_1 has deleted the comment of the user :full_name_2',

    'user_logged_in' => 'User :full_name (:email) successfully logged in',
    'admin_logged_in' => 'Admin :full_name (:email) successfully logged in',

    'user_created' => ':role :full_name (:email) created :changed_user_role',
    'user_updated' => ':role :full_name (:email) has made changes to the :changed_user_role details',
    'user_deleted' => ':role :full_name (:email) deleted :changed_user_role',
    'user_file_added' => ':role :full_name (:email) attached a file',
    'user_file_deleted' => ':role :full_name (:email) removed a file',
    'user_comment_created' => ':role :full_name (:email) has left a comment',
    'user_comment_deleted' => ':role :full_name (:email) has deleted a comment',
    'user_activated' => ':role :full_name (:email) has activated the :changed_user_role',
    'user_deactivated' => ':role :full_name (:email) has deactivated the :changed_user_role',

    'email_change_created' => 'User :full_name requested an email change :old_email => :new_email',
    'email_change_approved' => 'User :full_name (:email) approved email change :old_email => :new_email',
    'password_changed_by_admin' => 'User :admin_full_name (:admin_email) changed password of the user :user_full_name (:user_email)',
    'notification_settings_updated' => 'User :full_name (:email) has updated notification settings',
    'carrier_updated' => 'User :full_name (:email) has updated company profile',
    'carrier_info_photo_added' => 'User :full_name (:email) has added company logo',
    'carrier_info_photo_deleted' => 'User :full_name (:email) has deleted company logo',
    'carrier_w9_photo_added' => 'User :full_name (:email) has added W9 form',
    'carrier_w9_photo_deleted' => 'User :full_name (:email) has deleted W9 form',
    'carrier_usdot_photo_added' => 'User :full_name (:email) has added USDOT Certificate',
    'carrier_usdot_photo_deleted' => 'User :full_name (:email) has deleted USDOT Certificate',
    'carrier_insurance_updated' => 'User :full_name (:email) has updated company insurance info',
    'carrier_insurance_photo_added' => 'User :full_name (:email) has added Certificate of insurance',
    'carrier_insurance_photo_deleted' => 'User :full_name (:email) has deleted Certificate of insurance',
    'library_added_by_driver' => 'Driver :full_name (:email) has added new document to the library',
    'contact_created' => 'User :full_name (:email) has created the contact :contact_full_name',
    'contact_updated' => 'User :full_name (:email) has updated the contact :contact_full_name',
    'contact_deleted' => 'User :full_name (:email) has deleted the contact :contact_full_name',
    'news_created' => 'User :full_name (:email) has created a news record ":news_title"',
    'news_updated' => 'User :full_name (:email) has updated a news record ":news_title"',
    'news_deleted' => 'User :full_name (:email) has deleted a news record ":news_title"',
    'news_activated' => 'User :full_name (:email) has activated a news record ":news_title"',
    'news_deactivated' => 'User :full_name (:email) has deactivated a news record ":news_title"',
    'faq_created' => 'User :full_name (:email) has created a question ":question_en"',
    'faq_updated' => 'User :full_name (:email) has updated a question ":question_en"',
    'faq_deleted' => 'User :full_name (:email) has deleted a question ":question_en"',
    'library_created' => 'User :full_name (:email) has added a document ":document"',
    'library_deleted' => 'User :full_name (:email) has deleted a document ":document"',
    'order_deleted' => 'User :full_name (:email) deleted the order :load_id',
    'order_restored' => 'User :full_name (:email) restored the order :load_id from',
    'order_deleted_permanently' => 'User :full_name (:email) deleted the order :load_id permanently',
    'order_duplicated' => 'Order :new_load_id was duplicated from order :old_load_id',
    'order_changed' => ':role :full_name (:email) made changes to the order',

    'order_add_vehicle' => 'User :full_name (:email) has added vehicle',
    'order_delete_vehicle' => 'User :full_name (:email) has deleted vehicle',
    'order_delete_expense' => 'Expense was deleted',
    'order_delete_bonus' => 'Bonus was deleted',
    'order_delete_attachment' => 'Attachment was deleted',

    'status_changed_manually' => 'User :full_name has changed the order status from :status_old to :status_new',

    'on_deduct_from_driver' => 'User :full_name (:email) set the flag "Deduct from driver". Order :load_id',
    'on_deduct_from_driver_with_note' => 'User :full_name (:email) set the flag "Deduct from driver". Order :load_id. Note: :note',
    'off_deduct_from_driver' => 'User :full_name (:email) unset the flag "Deduct from driver". Order :load_id',

    'signed_inspection' => 'User :first_name :last_name (:email) signed :location inspection.',

    'sent_signature_link' => 'The link for signature the :location inspection was sent by :full_name (:email_sender) to :email_recipient',

    'delivered_signature_link_success' => 'The link for signature the :location inspection was delivered to :email_recipient',
    'delivered_signature_link_fail' => 'The link for signature the :location inspection was not delivered to :email_recipient',

    'payment_stage_added' => 'User :full_name (:email) added payment to the order',
    'payment_stage_deleted' => 'User :full_name (:email) deleted payment from the order',

    'vehicle_created' => ':role :full_name (:email) created :vehicle_type',
    'vehicle_updated' => ':role :full_name (:email) has made changes to the :vehicle_type details',
    'vehicle_file_deleted' => ':role :full_name (:email) removed a file',
    'vehicle_comment_created' => ':role :full_name (:email) has left a comment',
    'vehicle_comment_deleted' => ':role :full_name (:email) has deleted a comment',

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
    'vehicle_types' => [
        'truck' => 'Truck',
        'trailer' => 'Trailer',
        'vehicle' => 'Vehicle',
    ],
];
