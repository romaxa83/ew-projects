<?php

namespace App\GraphQL\Mutations\Admin\User;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\User\UserRepository;
use App\Services\Auth\UserPassportService;
use App\Services\Telegram\TelegramDev;
use App\Services\User\UserService;
use GraphQL\Error\Error;

class AdminConfirmEditUserPhone extends BaseGraphQL
{
    public function __construct(
        protected UserService $userService,
        protected UserRepository $userRepository,
        protected UserPassportService $passportService
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return array
     */
    public function __invoke($_, array $args) : array
    {
        $admin = \Auth::guard(Admin::GUARD)->user();
        try {
            $user = $this->userRepository->findByID($args['id']);

            // @todo запрос к 1с , на смену номера телефона
            $request1c = true;
            if ($request1c) {
//                $user = $this->userService->changePhone($user);
                // разлогиниваем и ждем верификацию нового телефона
                $this->passportService->logout($user);

                // @todo кинуть пуш пользователю о смене телефона
            } else {
                $this->userService->removeNewPhone($user);

                throw new \DomainException(__('error.1c.not confirm change phone'));
            }

            // @todo dev-telegram
            TelegramDev::info("Пользователю - ({$user->name}), изменен телефон", $admin->name);

            return $this->successResponse(__('message.phone change'));
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $admin->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}



