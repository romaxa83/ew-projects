<?php

use WezomCms\Core\Enums\TranslationSide;

return [
    TranslationSide::ADMIN => [
        'E-mail' => 'E-mail',
        'User' => 'Пользователь',
        'Users' => 'Пользователи',
        'User list' => 'Список пользователей',
        'User referrals' => 'Рефералы пользователя',
        'Referrals' => 'Рефералы',
        'Referral system' => 'Реферальная система',
        'Referrals number' => 'Кол-во рефералов',
        'Password' => 'Пароль',
        'Confirm password' => 'Подтвердить пароль',
        'Change password' => 'Изменить пароль',
        'Status' => 'Статус',
        'Phones' => 'Телефоны',
        'Full name' => 'ФИО',
        'Name' => 'Имя',
        'Surname' => 'Фамилия',
        'Patronymic' => 'Отчество',
        'Registration date' => 'Дата реєстрації',
        'Additionally' => 'Дополнительно',
        'Phone' => 'Телефон',
        'Login' => 'Авторизоваться',
        'log in as user' => 'Авторизоваться как пользователь',
        'Site' => 'Сайт',
        'Cabinet' => 'Кабинет',
        'Email verified' => 'Email или телефон подтверждены',
        'Yes' => 'Да',
        'No' => 'Нет',
        'Users active' => 'Активные пользователи',
        'Socials' => 'Соцсети',
        'Social links' => 'Ссылки на соцсети',
        'Facebook ID' => 'Facebook ID',
        'Application id' => 'Идентификатор приложения',
        'Facebook secret key' => 'Facebook секретный ключ',
        'Application secret' => 'Секрет приложения',
        'Facebook Redirect URI:' => 'Действительный URI перенаправления для OAuth:',
        'Google ID' => 'Google ID',
        'Google secret key' => 'Google секретный ключ',
        'Google Redirect URI:' => 'URI перенаправления:',
        'Twitter ID' => 'Twitter ID',
        'Twitter secret key' => 'Twitter секретный ключ',
        'Twitter Redirect URI:' => 'Callback Url:',
        'User with provided email already exists' => 'Пользователь с указанным e-mail уже существует',
        'User with provided phone already exists' => 'Пользователь с указанным телефоном уже существует',
        'SMS service' => 'SMS сервис',
        'Esputnik User' => 'Имя пользователя (eSputnik)',
        'Esputnik Password' => 'Пароль (eSputnik)',
        'Esputnik SMS sender name' => 'Имя отправителя для СМС (eSputnik)',
        'TurboSMS Login' => 'Логин (TurboSMS)',
        'TurboSMS Secret' => 'Секрет (TurboSMS)',
        'TurboSMS sender name' => 'Имя отправителя для СМС (TurboSMS)',
        'User orders' => 'Заказы пользователя',
        'social_links' => [
            'twitter' => 'Twitter',
            'telegram' => 'Telegram',
            'instagram' => 'Instagram',
            'whatsapp' => 'WhatsApp',
        ],
        'exception' => [
            'Invalid user status' => 'Invalid user status [:status]',
            'Not found user by phone' => 'Not found account by phone [:phone]'
        ],
        'tabs' => [
            'Main data' => 'Основные данные',
            'Orders' => 'Заказы',
        ],
        'notification' => [
            'register new user' => [
                'title' => "Зарегистрировался новый пользователь",
                'description' => "Зарегистрировался новый пользователь",
            ]
        ],
        'status' => [
            'Draft' => 'создан'
        ],
        'validation' => [
            'name' => [
                'required' => 'Имя пользователя обязательно'
            ],
            'surname' => [
                'required' => 'Фамилия пользователя обязательно'
            ],
            'email' => [
                'required' => 'Email обязателен',
                'unique' => 'Данный Email есть в системе',
            ],
            'phone' => [
                'required' => 'Телефон обязателен',
                'unique' => 'Данный телефон есть в системе',
            ],
            'actionToken' => [
                'required' => 'actionToken обязателен',
            ],
            'refreshToken' => [
                'required' => 'refreshToken обязателен',
            ],
        ],
        'passport' => [
            'exception' => [
                'invalid access token' => 'invalid access token',
                'not found record by access token' => 'not found record by access token',
            ]
        ],
        'message' => [
            'user logout' => 'Пользователь разлогинен',
            'user exist' => 'Пользователь есть',
            'delete profile' => 'Профиль удален',
            'can\'t delete profile' => 'Нет возможности удалить профиль'
        ],
        'referrals' => [
            'Bonus number' => 'Количество заказов, за которые начисляются бонусы',
            'Bonus sum' => 'Полученные бонусы',
            'Received bonuses' => 'Полученные бонусы (кол-во)',
            'Accrued bonuses sum' => 'Накопленные бонусы (сумма)',
            'Accrued bonuses' => 'Накопленные бонусы (кол-во)',
        ],
        'sms-drivers' => [
            'kazinfoteh' => [
                'Login' => 'Логин',
                'Password' => 'Пароль',
            ],
        ],
    ],
    TranslationSide::SITE => [
        'auth' => [
            'facebook' => 'Facebook',
            'google' => 'Google',
            'twitter' => 'Twitter',
            'Send Password Reset Link' => 'Отправить ссылку для сброса пароля',
            'Reset Password Notification' => 'Сбросить пароль',
            'Reset password receive text1' => 'Вы получаете это электронное письмо, потому что мы получили запрос на сброс пароля для вашей учетной записи.',
            'Reset Password' => 'Сбросить Пароль',
            'This password reset link will expire in :count minutes' => 'Эта ссылка для сброса пароля будет активна в течение :count минут.',
            'If you did not request a password reset no further action is required' => 'Если вы не запрашивали сброс пароля, никаких дополнительных действий не требуется.',
            'Come back' => 'Вернуться',
            'Verify Email Address' => 'Подтвердите адрес электронной почты',
            'Please click the button below to verify your email address' => 'Пожалуйста, нажмите кнопку ниже, чтобы подтвердить свой адрес электронной почты.',
            'If you did not create an account no further action is required' => 'Если вы не создали учетную запись, никаких дополнительных действий не требуется.',
            'Confirm' => 'Подтвердить',
            'You have successfully confirmed your email' => 'Вы успешно подтвердили свой е-mail',
            'To the specified email sent a link to reset your password' => 'На указанный e-mail выслана ссылка для восстановления пароля',
            'Email confirmation' => 'Подтверждение электронной почты',
            'A fresh verification link has been sent to your email address' => 'На ваш адрес электронной почты была отправлена новая ссылка для подтверждения.',
            'You are successfully logged in' => 'Вы успешно авторизовались на сайте.',
            'Authorisation Error Please try again' => 'Ошибка авторизации. Пожалуйста попробуйте еще раз',
            'Social network account successfully disabled!' => 'Аккаунт социальной сети успешно отключен',
            'Social network account added successfully!' => 'Аккаунт социальной сети успешно добавлен!',
            'This account is already linked to another user of the site!' => 'Этот аккаунт уже привязан к другому пользователю сайта.',
            'User is deactivated Please contact the site administration' => 'Пользователь деактивирован. Пожалуйста обратитесь к администрации сайта.',
            'Come back again!' => 'Возвращайтесь еще!',
            'Phone confirmation' => 'Подтверждение телефона',
            'You have successfully confirmed your phone' => 'Вы успешно подтвердили свой телефон',
            'A fresh verification code has been sent to your phone' => 'На ваш номер был отправлен новый код для подтверждения.',
            'Verification code' => 'Код подтверждения',
            'The code entered is incorrect' => 'Введенный код неверный',
            'Your verification code is: :code' => 'Код подтверждения телефона: :code',
            'Email or phone' => 'E-mail или телефон',
            'User with provided email already exists' => 'Пользователь с указанным e-mail уже существует',
            'User with provided phone already exists' => 'Пользователь с указанным телефоном уже существует',
            'User not found' => 'Пользователь не найден',
            'An error occurred while sending a message' => 'Возникла ошибка при отправке сообщения. Пожалуйста обратитесь к администрации сайта.',
            'To the specified phone sent a code to reset your password' => 'На указанный телефон выслан код для восстановления пароля',
            'Your password reset code is: :code' => 'Ваш код восстановления пароля: :code',
            'Thank you for registration' => 'Благодарим за регистрацию',
            'Personal cabinet' => 'Личный кабинет',
            'Thank you for registration Your password is: :password' => 'Благодарим за регистрацию. Ваш пароль: :password',
            'failed' => 'Имя пользователя и пароль не совпадают. Или пользователь деактивирован.',
            'throttle' => 'Слишком много попыток входа. Пожалуйста, попробуйте еще раз через :seconds секунд.',
            'Token' => 'Токен',
            'passwords' => [
                'password' => 'Пароль должен быть не менее восьми символов и совпадать с подтверждением.',
                'reset' => 'Ваш пароль успешно сброшен!',
                'sent' => 'Ссылка на сброс пароля была отправлена!',
                'token' => 'Ошибочный код сброса пароля.',
                'user' => 'Не удалось найти пользователя с указанным электронным адресом.',
                'throttled' => 'Пожалуйста, подождите перед повторной попыткой.',
            ],
            'code' => [
                'passwords' => [
                    'password' => 'Пароль должен быть не менее восьми символов и совпадать с подтверждением.',
                    'reset' => 'Ваш пароль успешно сброшен!',
                    'sent' => 'Ссылка на сброс пароля была отправлена!',
                    'token' => 'Ошибочный код сброса пароля.',
                    'user' => 'Не удалось найти пользователя с указанным электронным адресом.',
                ],
            ],
        ],
        'cabinet' => [
            'Code' => 'Код',
            'My account' => 'Мой кабинет',
            'Personal info' => 'Личные данные',
            'Edit personal info' => 'Редактировать личные данные',
            'Data successfully updated' => 'Ваши данные успешно обновлены',
            'Name' => 'Имя',
            'Surname' => 'Фамилия',
            'Patronymic' => 'Отчество',
            'E-mail' => 'E-mail',
            'Phone' => 'Телефон',
            'Login' => 'Логин',
            'Logout' => 'Выйти',
            'Password' => 'Пароль',
            'Current password' => 'Текущий пароль',
            'Old password' => 'Старый пароль',
            'New password' => 'Новый пароль',
            'Password confirmation' => 'Подтверждение пароля',
            'New password must be different from the old one' => 'Новый пароль должен отличаться от старого',
            'Password is entered incorrectly' => 'Текущий пароль введен не верно',
            'Password successfully changed' => 'Пароль успешно изменен',
        ],
        'referrals' => [
            'Use bonus' => 'Использовать бонусы',
            'Bonus accrue' => 'Начисление бонусов',
            'Bonus сancellation' => 'Списание бонусов',
        ],
        'validation' => [
            'name' => [
                'field' => 'Имя',
                'required' => 'Имя пользователя обязательно'
            ],
            'surname' => [
                'field' => 'Фамилия',
                'required' => 'Фамилия пользователя обязательно'
            ],
            'email' => [
                'field' => 'Email',
                'required' => 'Email обязателен',
                'unique' => 'Данный Email есть в системе',
            ],
            'phone' => [
                'field' => 'Телефон',
                'required' => 'Телефон обязателен',
                'unique' => 'Данный телефон есть в системе',
            ],
            'actionToken' => [
                'field' => 'Токен',
                'required' => 'actionToken обязателен',
            ],
            'refreshToken' => [
                'required' => 'refreshToken обязателен',
            ],
        ],
    ],
];
