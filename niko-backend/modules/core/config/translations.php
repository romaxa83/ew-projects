<?php

/**
 * Перед добавлением языка для админки - нужно:
 * - скачать файл перевода для плагина DataTables.
 * - проверить, есть ли переводы в плагинах: select2, bootstrap-datepicker, inits.js
 * - переводы валидации форм Laravel (resources/lang/{locale}/validation.php).
 * - Vee validate переводы в модуле catalog
 */

return [
    'call_functions' => ['lang', '__', 'trans_choice', 'trans'],
    'admin' => [
        'default' => 'ru',
        'locales' => [
            'ru' => ['name' => 'Русский'],
            // 'en' => ['name' => 'English'],
        ],
    ],
];
