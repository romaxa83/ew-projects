<?php

declare(strict_types=1);

use App\Enums\Orders\OrderDeliveryTypeEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Projects\Systems\WarrantyStatus;
use App\Enums\Solutions\SolutionClimateZoneEnum;
use App\Enums\Solutions\SolutionIndoorEnum;
use App\Enums\Solutions\SolutionSeriesEnum;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;

return [

    OrderDeliveryTypeEnum::class => [
        OrderDeliveryTypeEnum::GROUND => 'Terrestre',
        OrderDeliveryTypeEnum::NEXT_DAY => 'Al día siguiente',
        OrderDeliveryTypeEnum::OVERNIGHT => 'Nocturna',
    ],

    OrderStatusEnum::class => [
        OrderStatusEnum::CREATED => 'Creado',
        OrderStatusEnum::PENDING_PAID => 'Pendiente de pago',
        OrderStatusEnum::PAID => 'Pagado',
        OrderStatusEnum::SHIPPED => 'Enviado',
        OrderStatusEnum::CANCELED => 'Cancelado',
    ],

    SolutionTypeEnum::class => [
        SolutionTypeEnum::INDOOR => 'Indoor',
        SolutionTypeEnum::OUTDOOR => 'Outdoor',
    ],

    SolutionZoneEnum::class => [
        SolutionZoneEnum::SINGLE => 'Singlezone',
        SolutionZoneEnum::MULTI => 'Multizone',
    ],

    SolutionSeriesEnum::class => [
        SolutionSeriesEnum::SOPHIA => 'Sophia',
        SolutionSeriesEnum::SOPHIA_D => 'Sophia D',
        SolutionSeriesEnum::HYPER => 'Hyper',
    ],

    SolutionClimateZoneEnum::class => [
        SolutionClimateZoneEnum::HOT => 'Hot',
        SolutionClimateZoneEnum::MODERATE => 'Moderate',
        SolutionClimateZoneEnum::COLD => 'Cold',
    ],

    SolutionIndoorEnum::class => [
        SolutionIndoorEnum::WALL_MOUNT => 'Wall mount',
        SolutionIndoorEnum::CEILING_CASSETTE => 'Ceiling cassette',
        SolutionIndoorEnum::SLIM_DUCT => 'Slim duct',
        SolutionIndoorEnum::UNIVERSAL_FLOOR_CEILING => 'Universal floor ceiling',
        SolutionIndoorEnum::MINI_FLOOR_CONSOLE => 'Mini floor console',
        SolutionIndoorEnum::AIR_HANDLER_UNIT => 'Air handler unit (AHU)',
    ],

    WarrantyStatus::class => [
        WarrantyStatus::WARRANTY_NOT_REGISTERED => 'No registrado',
        WarrantyStatus::PENDING => 'Pendiente',
        WarrantyStatus::ON_WARRANTY => 'En garantía',
        WarrantyStatus::VOIDED => 'Anulada',
        WarrantyStatus::DENIED => 'Denegada',
    ],
];
