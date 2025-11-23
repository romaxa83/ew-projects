<?php

use WezomCms\Core\Enums\TranslationSide;

return [
    TranslationSide::ADMIN => [
        'file' => 'Файл',
        'message' => 'Сообщение',
        'uploader' => 'Загрузил',
        'imports' => 'Импорты',
        'import-product' => 'Импорт товаров',
        'status' => [
            'title' => 'Статус процесса',
            'new' => 'Новая',
            'done' => 'Загруженно',
            'failed' => 'Проваленно',
            'in_process' => 'В процессе',
        ],
        'exception' => [
            'import_in_process' => 'Загрузка в процессе, попробуйти поже'
        ]
    ],
    TranslationSide::SITE => [
    ],
];
