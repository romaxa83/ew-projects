<?php

namespace WezomCms\Users\Http\Controllers\Api\V1;

use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Requests\Services\Request1CService;
use WezomCms\TelegramBot\Telegram;
use WezomCms\Users\DTO\UserDto;
use WezomCms\Users\Http\Requests\Api\Auth\PhoneRequest;
use WezomCms\Users\Http\Requests\Api\User\ChangeStatusFrom1CRequest;
use WezomCms\Users\Http\Requests\Api\User\UpdateUserRequest;
use WezomCms\Users\Repositories\UserRepository;
use WezomCms\Users\Services\UserService;

class UserController extends ApiController
{
    private UserService $userService;
    private UserRepository $userRepository;
    private UserDto $userDto;

    public function __construct(
        UserService $userService,
        UserRepository $userRepository
    )
    {
        parent::__construct();

        $this->userService = $userService;
        $this->userRepository = $userRepository;
        $this->userDto = resolve(UserDto::class);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function user()
    {
        try {
            $user = \Auth::user();

            return $this->successJsonMessage(
                $this->userDto->setModel($user)->toArray()
            );

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(UpdateUserRequest $request)
    {
        try {

            $user = \Auth::user();

            $user = $this->userService->edit($request->all(), $user);

            return $this->successJsonMessage(
                $this->userDto->setModel($user)->toArray()
            );

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    // @todo подумать о новой верификации при смене номера
    public function changePhone(PhoneRequest $request)
    {
//        dd($request->all());
        try {

            $user = \Auth::user();
            $phone = $request['phone'];
            $comment = $request['comment'];

            $req = \App::make(Request1CService::class);
            $response = $req->userEdit($user, $phone);
            if($response['success']){
                $user = $this->userService->changePhone($phone, $user, $comment);
                return $this->successJsonMessage(
                    $this->userDto->setModel($user)->toArray()
                );
            }else{
                return $this->errorJsonMessage(__('cms-users::site.message.something wrong'));
            }

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @param ChangeStatusFrom1CRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(ChangeStatusFrom1CRequest $request)
    {
        try {
            Telegram::event('От 1с пришел запрос на смену сатуса для пользователя');
            Telegram::event(serialize($request->all()));

            $user = $this->userRepository->byId($request['AccountID'], [], 'id', false);
            if(!$user){
                return $this->successJsonCustomMessage(['success' => false, 'message' => 'not found user'], 404);
            }

            $this->userService->changeStatusFrom1C($user, $request);

            return $this->successJsonCustomMessage(['success' => true, 'message' => 'status changed'], 200);

        } catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }

    public function changeStatusTestMode(ChangeStatusFrom1CRequest $request)
    {
        try {
            Telegram::event('От 1с пришел ТЕСТОВЫЙ запрос на смену сатуса для пользователя');

            return $this->successJsonCustomMessage(['success' => true, 'message' => 'status changed'], 200);

        } catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }
}
