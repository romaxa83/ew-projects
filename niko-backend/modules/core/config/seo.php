<?php

return [
    'robots' => [
        'override' => true,
        'nofollow' => [
            'get_params' => [ // add meta tag robots with content "noindex, nofollow" when request has any parameter.
                'gclid',
                'utm_medium',
                'utm_source',
                'utm_campaign',
                'utm_content',
                'utm_term',
                '_openstat',
            ],
        ],
    ],
];
