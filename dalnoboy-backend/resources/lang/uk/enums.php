<?php

declare(strict_types=1);

use App\Enums\Clients\BanReasonsEnum;
use App\Enums\Permissions\AdminRolesEnum;
use App\Enums\Permissions\GuardsEnum;
use App\Enums\Permissions\UserRolesEnum;

return [
    GuardsEnum::class => [
        GuardsEnum::ADMIN => 'Адміністратор',
        GuardsEnum::USER => 'Користувач',
    ],

    UserRolesEnum::class => [
        UserRolesEnum::INSPECTOR => 'Інспектор',
    ],

    AdminRolesEnum::class => [
        AdminRolesEnum::SUPER_ADMIN => 'Супер-адміністратор',
        AdminRolesEnum::ADMIN => 'Адміністратор',
    ],

    BanReasonsEnum::class => [
        BanReasonsEnum::NON_PAYMENT => 'Регулярна несплата',
        BanReasonsEnum::VIOLATIONS => 'Постійні порушення',
    ],
];
