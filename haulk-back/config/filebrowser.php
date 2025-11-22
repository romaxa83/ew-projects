<?php

return [
    'need_auth' => env('FILE_BROWSER_NEED_AUTH', true),

    /*
     * Relative path to filebrowser directory
     */
    'root' => env('FILE_BROWSER_ROOT', 'filebrowser'),

    /*
     * Root directory name, without spaces
     */
    'root_name' => env('FILE_BROWSER_ROOT_NAME', 'default'),

    /*
     * digits after decimal point in file size
     */
    'file_size_accuracy' => env('FILE_BROWSER_FILE_SIZE_ACCURACY', 3),

    /*
     * Allowed to upload Image types
     */
    'mimetypes' => [
        'images' => [
            'jpeg',
            'jpg',
            'gif',
            'png',
            'bmp',
            'svg'
        ]
    ],

    'thumb' => [
        'dir_url' => env('THUMB_DIR_URL', env('APP_FRONTEND_URL') . '/assets/images/jodit/'),

        'mask' => env('THUMB_MASK', 'thumb-%s.svg'),

        'unknown_extension' => env('THUMB_UNKNOWN_EXTENSION', 'thumb-unknown.svg'),

        'exists' => [
            'doc',
            'docx',
            'gif',
            'jpeg',
            'jpg',
            'pdf',
            'png',
            'txt',
            'xls',
            'xlsx',
            'zip',
        ],
    ],

    'auth_token_parameter' => env('FILE_BROWSER_AUTH_TOKEN_PARAMETER', 'access_token'),

    'nesting_limit' => env('FILE_BROWSER_NESTING_LIMIT', 3),

    /*
     * Список типов файлов, которые редактор jodit переименовывает при загрузке
     */
    'jodit_broken_extension' => explode(
        ',',
        env('JODIT_BROKEN_EXTENSION', 'vnd,plain,msword')
    ),

    'cache' => [
        'key' => env('FILE_BROWSER_CACHE_KEY', 'filebrowser'),

        'duration' => env('FILE_BROWSER_CACHE_DURATION', 3600),
    ],
];
