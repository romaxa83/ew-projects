<?php

namespace App\Console\Commands\Helpers;

use App\Models\User\Car;
use App\Models\User\User;
use App\Services\AA\Client\RequestClient;
use App\Services\User\CarService;
use App\Services\User\UserService;
use Illuminate\Console\Command;

class SendDataToAA extends Command
{
    protected $signature = 'am:send_data_to_aa';

    protected $description = '';

    protected string $pathUserFind;
    protected string $pathUserCreate;

    protected string $pathCarFind;
    protected string $pathCarCreate;

    public function __construct(
        protected RequestClient $client,
        protected UserService $userService,
        protected CarService $carService,
    )
    {
        $this->pathUserFind = config("aa.request.get_user_by_phone.path");
        $this->pathUserCreate = config("aa.request.create_user.path");
        $this->pathCarFind = config("aa.request.get_car.path");
        $this->pathCarCreate = config("aa.request.create_car.path");

        parent::__construct();
    }

    public function handle()
    {
        try {
            $this->sendUserAndCar();

        } catch(\Throwable $e){
            $this->error('Message: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile());
            $this->error('Line: ' . $e->getLine());
        }
    }

    private function sendUserAndCar()
    {
        $models = User::query()
            ->with([
                'cars',
                'cars.user',
                'cars.brand',
                'cars.model',
            ])
//            ->where('id', 1157)
            ->whereNull('uuid')
            ->get();

        foreach ($models as $user){
            /** @var $user User */

            $pathFindUser =  $this->pathUserFind . $user->phone->formatAA();

            $this->info($pathFindUser);

            // делаем запрос на поиск пользователя
            $res = $this->client->getRequestWithoutException($pathFindUser);
            if(data_get($res, 'message') === "Client not found"){
                // делаем запрос на создание пользователя в АА, и обновляем у себя пользователя
                $data = $this->prepareUserData($user);

                $res = $this->client->postRequestWithoutException($this->pathUserCreate, $data);
                $this->userService->completeFromAA($user, data_get($res, 'data'));

                $this->info('send user [' . data_get($res, 'data.id') .']');
            } else {
                // обновляем у себя пользователя
                $user = $this->userService->completeFromAA($user, data_get($res, 'data.user'));
                $this->carService->createFromAA($user, data_get($res, 'data.vechilces'));

                $this->info('update user [' . data_get($res, 'data.user.id') .']');
            }

            $user->refresh();
            foreach ($user->cars as $car){
                /** @var $car Car */

                if(!$car->uuid){
                    $pathFindCar =  $this->pathCarFind . "vin={$car->vin}";
                    if($car->number){
                        $pathFindCar .= "&number={$car->number}";
                    }
                    $this->info($pathFindCar);

                    // делаем запрос на поиск авто
                    $res = $this->client->getRequestWithoutException($pathFindCar);
                    if(data_get($res, 'message') === "Auto not found"){

                        $data = $this->prepareCarData($car, $user);
                        $this->info('prepare car data .' . array_to_json($data));

                        $res = $this->client->postRequestWithoutException($this->pathCarCreate, $data);

                        $this->carService->completeFromAA($car, data_get($res, 'data'));
                        $this->info('create car [' . data_get($res, 'data.id') .']');

                    } else {
                        $this->carService->completeFromAA($car, data_get($res, 'data'));
                        $this->info('exists car [' . data_get($res, 'data.id') .']');
                    }
                }
            }

            $this->info('==================================================');
            usleep(200000);
        }
    }

    private function prepareUserData(User $user): array
    {
        return [
            'data' => [
                'id' => $user->uuid->getValue(),
                'name' => $user->name,
                'number' => $user->phone->formatAA(),
                'codeOKPO' => $user->egrpoy ?? '',
                'email' => $user->email ? $user->email->getValue() : '',
                'verified' => false,
            ]
        ];
    }

    private function prepareCarData(Car $car, User $user): array
    {
        return [
            'data' => [
                "id" => "",
                "name" => "",
                "brand" => $car->brand->uuid->getValue(),
                "model" => $car->model->uuid->getValue(),
                "year" => $car->year,
                "yearDeal" => "",
                "vin" => $car->vin->getValue(),
                "number" => $car->number->getValue(),
                "owner" => $user->uuid->getValue(),
                "personal" => $car->is_personal,
            ]
        ];
    }
}
