<?php

namespace WezomCms\Firebase\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Firebase\DTO\NotificationListDto;
use WezomCms\Firebase\Http\Requests\Api\FcmRequest;
use WezomCms\Firebase\Repositories\FcmNotificationRepository;
use WezomCms\Firebase\Types\FcmNotificationStatus;
use WezomCms\Firebase\UseCase\CallPushEvent;
use WezomCms\Users\Repositories\UserRepository;
use WezomCms\Users\Services\UserService;

class FirebaseController extends ApiController
{
    private UserService $userService;
    private UserRepository $userRepository;
    private FcmNotificationRepository $fcmNotificationRepository;

    public function __construct(
        UserService $userService,
        UserRepository $userRepository,
        FcmNotificationRepository $fcmNotificationRepository
    )
    {
        parent::__construct();
        $this->userService = $userService;
        $this->userRepository = $userRepository;
        $this->fcmNotificationRepository = $fcmNotificationRepository;
    }

    /**
     * @param FcmRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setToken(FcmRequest $request)
    {
        try {

            $user = $this->userRepository->byId($request['userId'], [], 'id', false);
            $this->userService->setFcmToken($request['token'], $user);

            return $this->successJsonMessage('set fcm token');
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function listNotification(Request $request)
    {
        try {
            $user = \Auth::user();

            $notifications = $this->fcmNotificationRepository->getAllByUser(
                $user->id,
                $request->all(),
                [FcmNotificationStatus::SEND, FcmNotificationStatus::CREATED],
                'desc'
            );

            $dtoList = resolve(NotificationListDto::class)
                ->setCount($this->fcmNotificationRepository->countByUser($user->id, [FcmNotificationStatus::SEND, FcmNotificationStatus::CREATED]))
                ->setCollection($notifications);

            return $this->successJsonMessage(
                $dtoList->toList()
            );
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function countNotification(Request $request)
    {
        try {
            if(!$request['from']){
                throw new \Exception('Not timestamp');
            }
            $user = \Auth::user();

            return $this->successJsonMessage(
                $this->fcmNotificationRepository->countByUserAndTime(
                    $user->id,
                    $request['from'],
                    [FcmNotificationStatus::SEND, FcmNotificationStatus::CREATED]
                )
            );
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function testSend($userId)
    {
        try {
            $userRepository = \App::make(UserRepository::class);
            $user = $userRepository->byId($userId, [], 'id', false);

            if(!$user){
                throw new \Exception('No found user');
            }

            if(!$user->fcm_token){
                throw new \Exception('No fcm_token');
            }

            CallPushEvent::test($user);
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }
}


