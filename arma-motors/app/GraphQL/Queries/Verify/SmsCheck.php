<?php

namespace App\GraphQL\Queries\Verify;

use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Services\Sms\Exceptions\SmsVerifyException;
use App\Repositories\Sms\SmsVerifyRepository;
use App\Services\Sms\SmsVerifyService;
use App\Services\Telegram\TelegramDev;

class SmsCheck extends BaseGraphQL
{
    public function __construct(
        private SmsVerifyRepository $smsVerifyRepository,
        private SmsVerifyService $smsVerifyService
    )
    {}

    /**
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return array
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
        $token = $args['smsToken'];
        $code = $args['smsCode'];

        try {
            $model = $this->smsVerifyRepository->findBySmsToken($token);

            if($model->sms_token->isExpiredToNow()){
                throw new SmsVerifyException(__('error.expired sms token'), ErrorsCode::SMS_TOKEN_EXPIRED);
            }

            if(!$model->equalsCode($code)){
                throw new SmsVerifyException(__('error.sms code not equals'), ErrorsCode::SMS_CODE_WRONG);
            }

            $model = $this->smsVerifyService->generateActionToken($model);

            return [
                'status' => true,
                'actionToken' => $model->action_token->getValue(),
                'message' => ''
            ];
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
