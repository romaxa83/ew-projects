<?php

namespace App\GraphQL\Mutations\User\Auth;

use App\Events\Firebase\FcmPush;
use App\Events\User\EmailConfirm;
use App\Exceptions\EmailVerifyException;
use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Models\Verify\EmailVerify;
use App\Repositories\Email\EmailVerifyRepository;
use App\Services\Email\EmailVerifyService;
use App\Services\Firebase\FcmAction;
use App\Services\Telegram\TelegramDev;
use App\Events\User\UserConfirmEmail as UserConfirmEmailEvents;
use GraphQL\Error\Error;

class UserConfirmEmail extends BaseGraphQL
{
    public function __construct(
        protected EmailVerifyRepository $emailVerifyRepository,
        protected EmailVerifyService $emailVerifyService,

    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return array
     */
    public function __invoke($_, array $args): array
    {
        try {
            $emailVerify = $this->emailVerifyRepository->findByToken($args['token']);

            /** @var $user User */
            $user = $emailVerify->entity;

            if($user->email_verify && $emailVerify->isVerify()){
                return $this->successResponse(__('message.email verify'));
            }

            $this->emailVerifyService->verify($emailVerify);

            // @todo кинуть пуш пользователю о удачной верификации

            // запрос в АА на редактирования пользователя
            if($user->uuid){
                event(new UserConfirmEmailEvents($user));
            }

            // @todo dev-telegram
            TelegramDev::info('Пользователь верифицировал email', $emailVerify->entity->name);

            return $this->successResponse(__('message.email confirm'));
        }
        catch(EmailVerifyException $e){

            if($e->getCode() === ErrorsCode::EMAIL_TOKEN_EXPIRED){
                if(EmailVerify::userEnabled()){
                    $emailVerify = $this->emailVerifyService->create($user, true);
                    event(new EmailConfirm($user, $emailVerify));
                    event(new FcmPush($user, FcmAction::create(FcmAction::EMAIL_VERIFY, [], $user)));

                    return $this->successResponse(__('message.send new email confirm'));
                }
            }

            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
        catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
