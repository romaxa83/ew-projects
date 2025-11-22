<?php

declare(strict_types=1);

use App\Enums\Clients\BanReasonsEnum;
use App\Enums\Permissions\AdminRolesEnum;
use App\Enums\Permissions\GuardsEnum;
use App\Enums\Permissions\UserRolesEnum;

return [
    GuardsEnum::class => [
        GuardsEnum::ADMIN => 'Администратор',
        GuardsEnum::USER => 'Пользователь',
    ],

    UserRolesEnum::class => [
        UserRolesEnum::INSPECTOR => 'Инспектор',
    ],

    AdminRolesEnum::class => [
        AdminRolesEnum::SUPER_ADMIN => 'Супер-администратор',
        AdminRolesEnum::ADMIN => 'Администратор',
    ],

    BanReasonsEnum::class => [
        BanReasonsEnum::NON_PAYMENT => 'Регулярная неуплата',
        BanReasonsEnum::VIOLATIONS => 'Постоянные нарушения',
    ],
];
