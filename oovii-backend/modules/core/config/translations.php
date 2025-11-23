<?php

/**
 * Перед добавлением языка для админки - нужно:
 * - скачать файл перевода для плагина DataTables.
 * - проверить, есть ли переводы в плагинах: select2, bootstrap-datepicker, inits.js
 * - прописать поддерживаемый Яндекс-картами язык в поле js_locale
 * - переводы валидации форм Laravel (resources/lang/{locale}/validation.php).
 * - Vee validate переводы в модуле catalog
 */

return [
    'call_functions' => ['lang', '__', 'trans_choice', 'trans'],
    'admin' => [
        'default' => 'ru',
        'locales' => [
            'ru' => ['name' => 'Русский', 'js_locale' => 'ru_RU'], // js_locale used in YandexMap
            // 'en' => ['name' => 'English', 'js_locale' => 'en_US'],
        ],
    ],
    'app' => [
        'default' => 'kk'
    ]
];
