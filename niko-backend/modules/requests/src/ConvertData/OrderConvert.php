<?php

namespace WezomCms\Requests\ConvertData;

use WezomCms\Core\UseCase\DateFormatter;
use WezomCms\Core\UseCase\PhoneFormatter;
use WezomCms\Requests\Helpers\SpareType;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\Users\Converts\CarNumberConvert;

class OrderConvert
{
    public static function toRequest(ServicesOrder $order): array
    {
        $order->load(['car.brand', 'car.model', 'car.transmission', 'car.engineType', 'dealership']);

        return [
            "AccountID" => $order->user->id,
            "ServiceTypeID" => SpareType::check($order->group->type, $order->service_id),
            "VIN" => $order->car->vin_code,
            "LicensePlate" => $order->car->number_for_1c ?? CarNumberConvert::for1c($order->car->number),
            "DealerID" => $order->dealership_id,
            "DealerName" => $order->dealership->name,
            "ApplicationID" => $order->id,
            "YearOfCarManufacture" => (int)$order->car->year,
            "ApplicationDateTime" => DateFormatter::convertDateToTimestampFor1s($order->created_at),
            "DesiredDateTime" => DateFormatter::convertDateToTimestampFor1s($order->on_date),
            "BrandID" => $order->car->brand->niko_id,
            "BrandName" => $order->car->brand->name,
            "TransmissionTypeID" => $order->car->transmission_id ?? null,
            "TransmissionTypeName" => $order->car->transmission->name ?? null,
            "ModelID" => $order->car->model->niko_id,
            "ModelName" => $order->car->model->name,
            "CarMileage" => (int)$order->mileage,
            "EngineTypeID" => $order->car->engine_type_id ?? null,
            "EngineTypeName" => $order->car->engineType->name ?? null,
            "EngineCapacity" => (int)$order->car->engine_volume ?? null,
            "FirstName" => $order->user->first_name,
            "MiddleName" => $order->user->patronymic,
            "FamilyName" => $order->user->last_name,
            "Email" => $order->user->email,
            "PhoneNumber" => PhoneFormatter::onlyNumber($order->user->phone),
            "Recall" => $order->recall ? 1 : 0,
            "Comment" => $order->comment
        ];
    }

    public static function fromResponse($data)
    {
        if($data){
            return  [
                "order_id" => $data['Data']['ApplicationID'],
                "status" => $data['Data']['ApplicationStatusID'],
            ];
        }

        return false;
    }
}
