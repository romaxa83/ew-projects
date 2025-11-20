<?php

namespace WezomCms\Promotions\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Firebase\UseCase\CallPushEvent;
use WezomCms\Promotions\DTO\PromotionsDto;
use WezomCms\Promotions\DTO\PromotionsListDto;
use WezomCms\Promotions\Http\Requests\Api\SetUsersFrom1CRequest;
use WezomCms\Promotions\Jobs\PromotionsPushJob;
use WezomCms\Promotions\Repositories\PromotionsRepository;
use WezomCms\Promotions\Services\PromotionsService;
use WezomCms\TelegramBot\Telegram;
use WezomCms\Users\Repositories\UserRepository;

class PromotionsController extends ApiController
{
    private PromotionsRepository $promotionsRepository;
    private PromotionsService $promotionsService;

    public function __construct(
        PromotionsRepository $promotionsRepository,
        PromotionsService $promotionsService
    )
    {
        parent::__construct();
        $this->promotionsRepository = $promotionsRepository;
        $this->promotionsService = $promotionsService;
    }


    public function list(Request $request)
    {
        try {
            $promotions = $this->promotionsRepository->getAllWithIndividual($request['userId']);

            $dtoList = \App::make(PromotionsListDto::class)
                ->setCollection($promotions)
                ->setExcludeFields(['webLink', 'description'])
            ;

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
    public function one($id)
    {
        try {
            $promotion = $this->promotionsRepository->byId($id);

            $dto = resolve( PromotionsDto::class)
                ->setModel($promotion);

            return $this->successJsonMessage($dto->toArray());

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function setUsers(SetUsersFrom1CRequest $request)
    {
        try {
            Telegram::event('От 1с пришел запрос на установку пользователей для индивидуальной акции');
            Telegram::event(serialize($request->all()));

            $promotion = $this->promotionsRepository->getByCode($request['ActionID']);
            if(!$promotion){
                return $this->successJsonCustomMessage(['success' => false, 'message' => 'not found promotion'], 404);
            }

            $this->promotionsService->setUsersToPromotionsFrom1c($promotion, $request);
            // задача для рассылки пуш-уведомлений пользователям, получившим индивидуальную акцию
            // @todo проверить , и подключить джобу на проде
//            dispatch(new PromotionsPushJob($request['ActionClients']));

            $userRepository = \App::make(UserRepository::class);
            $users = $userRepository->getUsersByIds($request['ActionClients']);

            foreach ($users as $user){
                CallPushEvent::newPromotion($user);
            }
//
            Telegram::event('Пуш уведомление, отправленно ( '. count($users) .' ) пользователям, по новой акции');

            return $this->successJsonCustomMessage(['success' => true, 'message' => 'promotions save'], 200);

        } catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }

    public function setUsersTestMode(SetUsersFrom1CRequest $request)
    {
        try {
            Telegram::event('От 1с пришел запрос ТЕСТОВЫЙ на установку пользователей для индивидуальной акции');

            return $this->successJsonCustomMessage(['success' => true, 'message' => 'promotions save'], 200);

        } catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }
}
