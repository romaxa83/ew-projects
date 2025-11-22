<?php

declare(strict_types=1);

use App\Enums\Clients\BanReasonsEnum;
use App\Enums\Permissions\AdminRolesEnum;
use App\Enums\Permissions\GuardsEnum;
use App\Enums\Permissions\UserRolesEnum;

return [
    GuardsEnum::class => [
        GuardsEnum::ADMIN => 'Admin',
        GuardsEnum::USER => 'User',
    ],

    UserRolesEnum::class => [
        UserRolesEnum::INSPECTOR => 'Inspector',
    ],

    AdminRolesEnum::class => [
        AdminRolesEnum::SUPER_ADMIN => 'Super Admin',
        AdminRolesEnum::ADMIN => 'Admin',
    ],

    BanReasonsEnum::class => [
        BanReasonsEnum::NON_PAYMENT => 'Regular non-payment',
        BanReasonsEnum::VIOLATIONS => 'Permanent violations',
    ],
];
