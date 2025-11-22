<?php

return [
    'comment' => [
        'created' => ':role :full_name (:email) has left a comment',
        'deleted' => ':role :full_name (:email) has deleted a comment',
    ],
    'media' => [
        'deleted' => ':role :full_name (:email) Attachment was deleted',
    ],
    'vehicle' => [
        'truck' => 'Truck',
        'trailer' => 'Trailer',

        'created' => ':role :full_name (:email) created :vehicle_type',
        'updated' => ':role :full_name (:email) has made changes to the :vehicle_type details',
        'file_deleted' => ':role :full_name (:email) removed a file',
        'comment_created' => ':role :full_name (:email) has left a comment',
        'comment_deleted' => ':role :full_name (:email) has deleted a comment',
    ],
    'inventory' => [
        'created' => 'Inventory :stock_number was created',
        'updated' => ':role :full_name (:email) made changes to the inventory',
        'quantity_increased' => ':role :full_name (:email) increased quantity of inventory :inventory_name, :stock_number',
        'quantity_decreased' => ':role :full_name (:email) decreased quantity of inventory :inventory_name, :stock_number',
        'quantity_decreased_sold' => ':role :full_name (:email) decreased quantity of inventory :inventory_name, :stock_number. Invoice for :inventory_name, :stock_number was paid.',
        'quantity_reserved_for_order' => ':role :full_name (:email) reserved inventory :inventory_name, :stock_number at a price of :price for the order <a href=":order_link">:order_number</a>',

        'quantity_reserved_additionally_for_order' => ':role :full_name (:email) reserved additional quantity of inventory :inventory_name, :stock_number at a price of :price for the order <a href=":order_link">:order_number</a>',
        'quantity_reduced_from_order' => ':role :full_name (:email) reduced reserved quantity of inventory :inventory_name, :stock_number at a price of :price for the order <a href=":order_link">:order_number</a>',
        'price_changed_for_order' => ':role :full_name (:email) reserved quantity of inventory :inventory_name, :stock_number at a price of :price for the order <a href=":order_link">:order_number</a>',
        'finished_order' => ':role :full_name (:email) finished order <a href=":order_link" target="_blank">:order_number</a> using inventory :inventory_name, :stock_number at a price of :price',
        'quantity_returned_for_deleted_order' => ':role :full_name (:email) returned inventory :inventory_name, :stock_number for the deleted order :order_number',
    ],
    'order' => [
        'common' => [
            'created' => 'Order :order_number was created',
            'updated' => ':role :full_name (:email) made changes to the order',
            'deleted' => ':role :full_name (:email) deleted the order :order_number',
            'status_changed' => ':role :full_name (:email) marked the order as :status',
            'created_payment' => ':role :full_name (:email) added payment to the order :order_number',
            'deleted_payment' => ':role :full_name (:email) deleted payment from the order :order_number',
            'send_docs' => 'Invoice was sent by :role :full_name (:email) to :receivers',
        ],
        'bs' => [
            'restored' => ':role :full_name (:email) restored the order :order_number',
            'attached_document' => ':full_name attached a file to the order',
            'reassigned_mechanic' => 'Mechanic :mechanic_name is assigned to order :order_number by :role :full_name',
            'status_changed' => ':role :full_name (:email) marked the order as :status',
        ],
        'parts' => [
            'assign_sales_manager' => ':role :full_name (:email) marked the order as In process and assigned :sales_manager_name to order.',
            'reassign_sales_manager' => 'Sales manager :sales_manager_name is assigned to order :order_number by :role :full_name',
            'add_item' => ':role :full_name (:email) added new inventory ":inventory_name" to order :order_number',
            'update_item' => ':role :full_name (:email) update inventory ":inventory_name" to order :order_number',
            'delete_item' => ':role :full_name (:email) deleted inventory ":inventory_name" from order :order_number',
            'refunded' => ':role :full_name (:email) the order :order was refunded',
            'update_delivery' => ':role :full_name (:email) edited the order Delivery info',
            'send_payment_link' => 'Payment link was sent by :role :full_name (:email) to :client_name (:client_email)',
        ]
    ]
];
