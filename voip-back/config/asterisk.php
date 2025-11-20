<?php

return [
    // данные для подключения к клиенту ami
    'ami' => [
        'host' => env('AMI_HOST'),
        'port' => env('AMI_PORT'),
        'username' => env('AMI_USERNAME'),
        'secret' => env('AMI_SECRET'),
        'connect_timeout' => env('AMI_CONNECT_TIMEOUT', 10000),
        'read_timeout' => env('AMI_READ_TIMEOUT', 10000),
        'connect_schema' => env('AMI_CONNECT_SCHEMA', 'tcp://'), // try tls://
    ],
    // данные для подключения к клиенту ari
    'ari' => [
        'host' => env('ARI_HOST'),
        'port' => env('ARI_PORT', 8088),
        'username' => env('ARI_USERNAME'),
        'password' => env('ARI_PASSWORD'),
        'ws_interval' => env('ARI_WS_INTERVAL', 3), // интервал (сек.) для websocket
        'is_debug' => env('ARI_DEBUG', false),
        'logger_enable' => env('ENABLE_ARI_LOGGER', false),
        'settings' => [
            'connect_timeout' => env('ARI_CONNECT_TIMEOUT', 10),
        ]
    ],
    'ami_demon' => [
        // команда для перезапуска слушателя событий
        'restart_command' => env('AMI_RESTART_LISTENER_COMMAND', false),
        // кол-во попыток, если есть активные звонки
        'restart_try' => env('AMI_RESTART_LISTENER_TRY', 3),
        // сколько ждать сек. перед повторной попыткой
        'restart_sleep' => env('AMI_RESTART_LISTENER_SLEEP', 2)
    ],
    // логировать события ami
    'ami_logger_enable' => env('ENABLE_PAMI_LOGGER', false),
    // команда для перезапуска слушателя событий
    'call_record_url' => env('ASTERISK_AUDIO_HOST', 'http://127.0.0.1'),
    // значения для таблицы "queue_member"
    'queue_member' =>  [
        'wrapuptime' =>  env('ASTERISK_QUEUE_MEMBER_WRAPUPTIME', 10),
        'ringinuse' =>  env('ASTERISK_QUEUE_MEMBER_RINGINUSE', 'no'),
    ],
    // значения для таблицы "queue"
    'queue' =>  [
        'musiconhold' =>  env('ASTERISK_MUSICONHOLD', 'default'),
        'timeout' =>  env('ASTERISK_TIMEOUT', 10),
        'queue_timeout' =>  env('ASTERISK_QUEUE_TIMEOUT', 60),
        'ringinuse' =>  env('ASTERISK_RINGINUSE', 'no'),
        'setinterfacevar' =>  env('ASTERISK_SETINTERFACEVAR', 'yes'),
        'setqueuevar' =>  env('ASTERISK_SETQUEUEVAR', 'yes'),
        'setqueueentryvar' =>  env('ASTERISK_SETQUEUEENTRYVAR', 'yes'),
        'announce_frequency' =>  env('ASTERISK_ANNOUNCE_FREQUENCY', 30),
        'announce_to_first_user' =>  env('ASTERISK_ANNOUNCE_TO_FIRST_USER', 'yes'),
        'announce_position_limit' =>  env('ASTERISK_ANNOUNCE_POSITION_LIMIT', 100),
        'announce_holdtime' =>  env('ASTERISK_ANNOUNCE_HOLDTIME', 'no'),
        'announce_position' =>  env('ASTERISK_ANNOUNCE_POSITION', 'yes'),
        'periodic_announce_frequency' =>  env('ASTERISK_PERIODIC_ANNOUNCE_FREQUENCY', 0),
        'relative_periodic_announce' =>  env('ASTERISK_RELATIVE_PERIODIC_ANNOUNCE', 'yes'),
        'retry' =>  env('ASTERISK_RETRY', 1),
        'wrapuptime' =>  env('ASTERISK_WRAPUPTIME', 10),
        'autofill' =>  env('ASTERISK_AUTOFILL', 'no'),
        'autopause' =>  env('ASTERISK_AUTOPAUSE', 'no'),
        'autopausebusy' =>  env('ASTERISK_AUTOPAUSEBUSY', 'no'),
        'autopauseunavail' =>  env('ASTERISK_AUTOPAUSEUNVAIL', 'no'),
        'maxlen' =>  env('ASTERISK_MAXLEN', 0),
        'servicelevel' =>  env('ASTERISK_SERVICELEVEL', 10),
        'strategy' =>  env('ASTERISK_STRATEGY', 'rrmemory'),
        'leavewhenempty' =>  env('ASTERISK_LEAVEWHENEMPTY', 'no'),
        'reportholdtime' =>  env('ASTERISK_REPORTHOLDTIME', 'no'),
        'memberdelay' =>  env('ASTERISK_MEMBERDELAY', 0),
        'weight' =>  env('ASTERISK_WEIGHT', 0),
        'timeoutrestart' =>  env('ASTERISK_TIMEOUTRESTSRT', 'yes'),
        'timeoutpriority' =>  env('ASTERISK_TIMEOUTPRIORITY', 'conf'),
        'language_default' =>  env('ASTERISK_LANGUAGE_DEFAULT', 'en'),
    ],
    'music' => [
        'hold_to_end_work_day' => env('MUSIC_HOLD_TO_END_WORK_DAY', 30), // За сколько минут до конца рабочего времени отключаем кастомные мелодии
        'prefix_for_name' => env('MUSIC_PREFIX_FOR_NAME', 'wezom_crm/'),   // Префикс перед название файлы в бд asterisk
    ]
];
