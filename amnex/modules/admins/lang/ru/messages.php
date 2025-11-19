<?php

use Wezom\Core\Enums\TranslationSideEnum;

return [
    TranslationSideEnum::ADMIN => [
        'admin_fail_reasons_by_myself' => 'Вы не можете удалить себя.',
        'forgot_password' => [
            'greeting' => 'Привет, :name!',
            'subject' => 'Сброс пароля',
            'line_1' => 'Поступил запрос на изменение пароля!',
            'line_2' => 'Если вы не отправляли этот запрос, проигнорируйте это письмо.',
            'line_3' => 'В противном случае нажмите эту ссылку, чтобы изменить свой пароль:
        <a href=":link" style="color: red">Ссылка</a>',
            'action' => 'Установить пароль',
        ],

        'email_verification' => [
            'greeting' => 'Привет, :name',
            'subject' => 'Подтверждение электронной почты',
            'line' => 'Чтобы подтвердить новое письмо, перейдите по ссылке',
            'action' => 'Подтвердить',
        ],

        'set_password' => [
            'greeting' => 'Привет, :name!',
            'subject' => 'Создать пароль',
            'line_1' => 'Ваша учетная запись создана! Пожалуйста, установите пароль.',
            'line_2' => 'Если вы не создавали учетную запись, проигнорируйте это письмо.',
            'line_3' => 'В противном случае нажмите эту ссылку, чтобы установить пароль:
        <a href=":link" style="color: red">Link</a>',
            'action' => 'Set password',

        ],
    ],
];
