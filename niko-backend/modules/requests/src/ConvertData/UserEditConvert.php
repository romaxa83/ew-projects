<?php

namespace WezomCms\Requests\ConvertData;

use WezomCms\Core\UseCase\PhoneFormatter;
use WezomCms\Users\Models\Car;
use WezomCms\Users\Models\User;
use WezomCms\Users\Types\LoyaltyLevel;
use WezomCms\Users\Types\LoyaltyType;

class UserEditConvert
{
    public static function toRequest(User $user, $newPhone): array
    {
//        return [
//            "AccountID" => 1,
//            "FirstName" => "Иван",
//            "FamilyName" => "Иванов",
//            "MiddleName" => 'Иванович',
//            "PhoneNumber" => "+38(095)451-4991"
//        ];

        return  [
            "AccountID" => $user->id,
            "FirstName" => $user->first_name,
            "FamilyName" => $user->last_name,
            "MiddleName" => $user->patronymic,
            "PhoneNumber" => PhoneFormatter::onlyNumber($newPhone),
        ];
    }

    public static function fromResponse($data)
    {

        if($data){
            return  [
                "success" => $data['Data']['RegistrationSuccess'] === 1 ? true : false,
            ];
        }
        return false;
    }
}

