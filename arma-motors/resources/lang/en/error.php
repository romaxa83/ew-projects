<?php

return [
    'not found admin' => 'Админ не найден',
    'not found user' => 'Пользователь не найден',
    'not found model' => 'Модель не найдена',
    'not found user car' => 'Машина пользователя не найдена',
    'not found aa_post' => 'Не найден пост для дц',
    'not found record by access token' => 'Не найдена запись по \'oauth_access_tokens\'',

    'not found record by sms token' => 'Не получается найти запись по sms-token - :sms_token',
    'not found record by action token' => 'Не получается найти запись по actionToken - :action_token',
    'sms code not equals' => 'Некорректный смс код',
    'expired sms token' => 'Истек smsToken',
    'expired action token' => 'Истек actionToken',
    'active action token' => 'Активен actionToken',
    'active sms token' => 'Активен smsToken',
    'sms verify not have required field' => 'When requesting for verification, you need to transfer either the phone or the accessToken of the user',
    'invalid access token' => 'Не валидный accessToken',

    'wrong old password' => 'Неверный старый пароль',
    'user not have new phone' => 'У пользователя нет нового телефона',

    'field must exist' => 'Поле должно ":field" быть',
    'field can\'t be null' => 'Поле ":field" не должно быть null',
    'field can\'t be empty' => 'Поле ":field" не должно быть пустым',

    'email_verify' => [
        'not check model' => 'Нет возможности сгенерировать - :model',
        'not found setting for model' => 'Не найдены настройки для данной модели - :model',
        'active email token' => 'Верефикационый токен еще активен',
        'not found record by token' => 'Нет модели по токену - :token',
        'expired email token' => 'Токен истек',
        'not email' => 'У пользователя нет почты',
        'email verify' => 'Почта верефицирована',
        'have active token' => 'Есть активный токен',
    ],

    'have user by this phone' => 'There is a user with this phone- :phone',
    'new phone equals old' => 'Your new phone (:newPhone) equals old phone (:oldPhone)',

    '1c' => [
        'not confirm change phone' => '1c did not confirm the change of user\'s phone'
    ],
    'must deactivate model before delete' => 'It is necessary to deactivate the model before deleting',
    'model not trashed' => 'model not trashed',
    'brand' => [
        'not defined color' => 'For the brand, this color (:color) is not defined'
    ],
    // car
    'car with number exist' => 'The car with this number ":number" is already in the system',
    'car must be verify' => 'The car must be verified',
    'car exist to archive' => 'The car exists in the archive',
    'car have insurance' => 'The car already has insurance',
    'can\'t change to verify status' => 'Only "AA" can transfer to this status',

    'dealership' => [
        'not data for department sales' => 'No data on sales department',
        'not data for department service' => 'No data on the service department',
        'not data for department credit' => 'No data on insurance and lending',
        'not data for department body' => 'No data on the body department'
    ],

    'order' => [
        'must close status' => 'The application must be in the "Closed" status',
        'order have file' => 'The application has a file of the type - :type',
        'order not support brand' => 'This type of application does not support the brand - :brand',
        'order not support action' => 'This type of application does not support the action',
        'order must be create and process status' => 'Заявка должна быть в "создана" или "в работе"',
        'real time is busy' => 'Данное время уже занято',
        'order must have dealership' => 'Заявка обязана иметь привязаный дилерский центр',
        'not change status order close' => 'Заявка в статусе "Закрыта", ей нельзя сменить статус',
        'car must be verify' => 'Заявку можно создать только для верифицированного авто',
        'free time' => [
            'not support this service' => 'Данный сервис не поддерживается',
            'not have schedule' => 'Нет графика работ'
        ]
    ],

    'hash alias not valid' => 'The alias ":alias" is not valid for receiving the hash',
    'not valid communication type' => 'This type of communication is not supported (:type)',
    'not valid user type' => 'This user type is not supported (:type)',
    'not valid car status' => 'Invalid auto status (:status)',
    'not valid user status' => 'Invalid user status (:status)',
    'not valid user password' => 'Invalid user password',
    'not valid message status' => 'Invalid message status (:status)',
    'not valid order status' => 'Invalid order status (:status)',
    'not valid order payment status' => 'Invalid order payment status (:status)',
    'not valid car type for spares' => 'Invalid car type for spare parts (:type)',

    'required comment for delete car' => 'The given reason - (:reason), implies the presence of a comment',
    'admin not delete car' => 'You can delete a car only after the user has deleted it',
    'required comment to rate' => 'With this rating - ":rate", a comment is required',
    'not manipulate by this user' => 'Сannot be manipulated on the given user',

    'can\'t delete support message' => 'To delete a request, it must have the status "completed"',

    'mobile_token' => [
        'incorrect refresh token' => 'Missing or empty refreshToken',
        'incorrect device id' => 'Missing or empty deviceId',
        'not equals device id' => 'DeviceId not equals',
    ],

    'page have file' => 'The page has an attached file',

    'file empty' => 'File not transferred',
    'file upload with error' => 'File uploaded with error',

    'translations' => [
        'not defined place' => 'Place :place , unknown'
    ],

    'incorrect data for convert date' => 'Некорректный данные [:data] для конвертации времени',
];
