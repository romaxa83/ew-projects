<?php

namespace WezomCms\Dealerships\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Core\Traits\CoordsTrait;
use WezomCms\Dealerships\DTO\DealershipListDto;
use WezomCms\Dealerships\DTO\DealershipListFor1CDto;
use WezomCms\Dealerships\Repositories\DealershipRepository;
use WezomCms\Dealerships\DTO\DealershipDto;
use WezomCms\TelegramBot\Telegram;

class DealershipsController extends ApiController
{
    use CoordsTrait;

    private DealershipRepository $dealershipRepository;

    public function __construct(DealershipRepository $dealershipRepository)
    {
        parent::__construct();
        $this->dealershipRepository = $dealershipRepository;
    }

    public function all(Request $request)
    {
        try {

            $dealerships = $this->dealershipRepository->getAll(
                ['city', 'schedule', 'gallery', 'brand'], 'sort', $request->all(), false
            );

            $dtoList = resolve(DealershipListDto::class)
                ->setCount($this->dealershipRepository->getAllOnlyCount(['city', 'brand'], 'id', $request->all(), false))
                ->setCollection($dealerships)
                ->setExcludeFields(['servicesDescription', 'description'])
            ;

            if($this->checkFromRequest($request->all())){
                $dtoList->setPoint($this->getPoint());
            }

            return $this->successJsonMessage(
                $dtoList->toList()
            );

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function one(Request $request, $id)
    {
        try {
            $dealership = $this->dealershipRepository->byId($id, [
                'city', 'scheduleSalon', 'scheduleService', 'gallery', 'brand', 'brand.modelsForTrade'
            ]);

            $dto = resolve(DealershipDto::class)
                ->setModel($dealership);

            if($this->checkFromRequest($request->all())){
                $dto->setCoordsForDistance($this->getPoint());
            }

            return $this->successJsonMessage($dto->toArray());

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function listFor1C()
    {
        try {
            Telegram::event('От 1с пришел запрос на СПИСОК ДЦ');

            $dealerships = $this->dealershipRepository->getAll(['brand'],'id',['size' => 50],false);

            $dto = \App::make(DealershipListFor1CDto::class)
                ->setCollection($dealerships);

            return $this->successJsonCustomMessage(['success' => true, 'data' => $dto->toList()], 200);

        } catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }
}
