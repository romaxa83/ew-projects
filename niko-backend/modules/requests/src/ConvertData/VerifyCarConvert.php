<?php

namespace WezomCms\Requests\ConvertData;

use WezomCms\Core\UseCase\PhoneFormatter;
use WezomCms\Users\Models\Car;
use WezomCms\Users\Types\LoyaltyLevel;
use WezomCms\Users\Types\LoyaltyType;

class VerifyCarConvert
{
    public static function toRequest(Car $car): array
    {
        return  [
            "AccountID" => $car->user_id,
            "FirstName" => $car->user->first_name,
            "FamilyName" => $car->user->last_name,
            "MiddleName" => $car->user->patronymic,
            "PhoneNumber" => PhoneFormatter::onlyNumber($car->user->phone),
            "Email" => $car->user->email,
            "BrandID" => $car->brand->niko_id,
            "TransmissionTypeID" => $car->transmission_id,
            "VIN" => $car->vin_code,
            "YearOfCarManufacture" => $car->year,
            "ModelID" => $car->model->niko_id,
            "LicensePlate" => $car->number_for_1c,
            "EngineCapacity" => $car->engine_type_id,
        ];
    }

    public static function fromResponse($data)
    {
        if($data){
            return  [
                "account_status" => $data['Data']['AccountStatusID'],
                "car_status" => $data['Data']['VehicleStatusID'],
                "description" => $data['Data']['DescriptionExceptions'],
                "loyalty_type" => isset($data['Data']['LoyaltyProgramTypeID'])
                    ? $data['Data']['LoyaltyProgramTypeID']
                    : LoyaltyType::NONE ,
                "loyalty_level" => isset($data['Data']['LoyaltyLevelID'])
                    ? $data['Data']['LoyaltyLevelID']
                    : LoyaltyLevel::NONE ,
                "level_up_amount" => isset($data['Data']['LevelUpAmount'])
                    ? $data['Data']['LevelUpAmount']
                    : null,
                "buy_cars" => isset($data['Data']['PurchasedСars'])
                    ? $data['Data']['PurchasedСars']
                    : 0,
            ];
        }

        return false;
    }
}
