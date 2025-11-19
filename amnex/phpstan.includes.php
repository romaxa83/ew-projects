<?php

declare(strict_types=1);

$includes = [];

foreach (glob('modules/*/phpstan.neon') as $file) {
    if (file_exists($file)) {
        $includes[] = $file;
    }
}

$config = [];
$config['includes'] = $includes;

return $config;
