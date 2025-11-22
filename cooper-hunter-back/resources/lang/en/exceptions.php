<?php

return [
    'assert_data' => [
        'field must exist' => 'field ":field" must exist',
        'field not null' => 'field ":field" can\'t be null',
        'field not empty' => 'field ":field" can\'t be empty',
    ],

    'not trashed' => [
        'technician' => 'Can\'t restore technician because he is not trashed'
    ],
    'payment' => [
        'card' => [
            'exist' => "This payment card has already been added",
            'not exist' => 'The user does not have this payment card'
        ]
    ],
    'company' => [
        'not found by guid' => "Order by guid [:guid] not found"
    ],
    'dealer' => [
        'not_main' => "Data can only be obtained by the main dealer",
        'not_action_for_main' => "You cannot do this action",
        'order' => [
            'not found by guid' => "Order by guid [:guid] not found",
            'not has invoice' => "It is not possible to generate an invoice for this order [:guid]",
            'item is already on order' => "This item is already on order.",
            'can\'t this order' => "You can't edit this order.",
            'order is not draft' => "To edit the status must be in the status - draft",
            'onec not create an order' => "The order can not be sent, please try later.",
            'packing_slip' => [
                'not found by guid' => "Packing slip by guid [:guid] not found",
                'not update by onec' => "Failed to update a packing slip, please try later",
            ]
        ]
    ],
    'commercial' => [
        "technician does\'n have a commercial certificate" => 'This technician does\'n have a commercial certificate',
        'quote' => [
            'incorrect switching status' => 'incorrect switching status'
        ],
        'commissioning' => [
            'not create option answer by question' => 'Can\'t create option answers for this question',
            'answer must contain a text field' => 'This answer must contain a text field',
            'answer must contain a radio field' => 'This answer must contain a radio field',
            'answer must contain a media' => 'This answer must contain a media',
            'answer must contain an options answer field' => 'This answer must contain an options answer field',
            'answer must contain one option answer' => 'This answer must contain one option answer',
            'option answer does not apply to this question' => 'This option answer does not apply to this question',
            'this protocol is closed' => 'This protocol is closed',
            'commissioning for this project is closed' => 'Commissioning for this project is closed',
            'commissioning not started yet' => 'Commissioning not started yet, close everything pre commissioning',
            'question does not contain an answer' => 'This question does not contain an answer',
            'can\'t toggle status' => "Can't switch status from ':from_status' to ':to_status'",
            'can\'t delete question' => "You can't delete a question only if it is in the status 'draft'",
            'can\'t create an option answer' => "You can't create an option answer, because his question isn't a draft",
            'can\'t update an option answer' => "You can't update an option answer, because his question isn't a draft",
            'can\'t delete an option answer' => "You can't delete an option answer, because his question isn't a draft",
        ],
        'addition' => [
            'exist' => 'Can\'t create data, it already exists',
            'can\'t update' => "You can't update a commercial project addition",
            'can\'t remove' => "You can't remove a commercial project addition"
        ],
        'warranty' => [
            'not closed commissioning' => 'not closed commissioning',
            'not have units' => 'This project has not units',
            'not have additions' => 'This project has not additions',
            'exist' => 'This project has a warranty registration',
            "must_be_monoblock_or_outdoor" => "Serial number must be a monoblock or outdoor",
            "monoblock_must_be_one" => "Monoblock must consist of one serial-number",
            "outdoor_has_no_sub_type" => "Outdoor has no sub type",
            "outdoor_single_has_more_indoor" => "Outdoor with type single must have only one indoor",
            "outdoor_multi_consist_indoor" => "Outdoor with type multi must contain from 2 to 5 indoors",
            "outdoor_multi_consist_not_indoor" => "Outdoor with type multi must have no indoor",
        ],
    ]
];
