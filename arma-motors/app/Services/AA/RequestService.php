<?php

namespace App\Services\AA;

use App\Models\Agreement\Agreement;
use App\Models\Order\Order;
use App\Models\User\Car;
use App\Models\User\User;
use App\Services\AA\Commands\AcceptAgreement;
use App\Services\AA\Commands\CreateCar;
use App\Services\AA\Commands\CreateOrder;
use App\Services\AA\Commands\CreateUser;
use App\Services\AA\Commands\GetAct;
use App\Services\AA\Commands\GetCar;
use App\Services\AA\Commands\GetInvoice;
use App\Services\AA\Commands\GetUserByPhone;
use App\Services\AA\Commands\UpdateUser;

class RequestService
{
    public function __construct()
    {}

    public function getUserByPhone(User $user)
    {
        return resolve(GetUserByPhone::class)->handler($user);
    }

    public function createUser(User $user)
    {
        return resolve(CreateUser::class)->handler($user);
    }

    public function updateUser(User $user)
    {
        return resolve(UpdateUser::class)->handler($user);
    }

    public function deleteUser(User $user)
    {
        // todo реализовать, когда 1с реализует метод
    }

    public function getCarFromAA(Car $car)
    {
        return resolve(GetCar::class)->handler($car);
    }

    public function createCar(Car $car)
    {
        return resolve(CreateCar::class)->handler($car);
    }

    public function createOrder(Order $order)
    {
        return resolve(CreateOrder::class)->handler($order);
    }

    public function acceptAgreement(Agreement $model)
    {
        return resolve(AcceptAgreement::class)->handler($model);
    }

    public function getDataInvoice(string $uuid)
    {
        return resolve(GetInvoice::class)->handler($uuid);
    }

    public function getDataAct(string $uuid)
    {
        return resolve(GetAct::class)->handler($uuid);
    }
}

