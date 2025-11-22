<?php

namespace App\Foundations\Enums;

enum CacheKeyEnum: string {
    case Languages = "languages";
    case Languages_default = "languages_default";
    case Translations = "translations";
    case Roles = "roles";
    case States = "states";
}
