<?php

namespace WezomCms\Users\Http\Controllers\Api\V1;

use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\TelegramBot\Telegram;
use WezomCms\Users\DTO\CarListDto;
use WezomCms\Users\DTO\LoyaltyDto;
use WezomCms\Users\Http\Requests\Api\User\AddCarRequest;
use WezomCms\Users\Http\Requests\Api\Car\CarChangeStatusFrom1CRequest;
use WezomCms\Users\Models\Car;
use WezomCms\Users\Repositories\CarRepository;
use WezomCms\Users\Services\UserCarService;

class LoyaltyController extends ApiController
{

    public function __construct(
    )
    {
        parent::__construct();

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function loyalty()
    {
        try {
            $user = \Auth::user();

            $user->load(['loyalty', 'loyalty.levelInfo']);

            $dto = \App::make(LoyaltyDto::class)->setModel($user);

            return $this->successJsonMessage($dto->toArray());

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }
}

