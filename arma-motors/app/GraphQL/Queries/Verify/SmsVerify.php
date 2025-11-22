<?php

namespace App\GraphQL\Queries\Verify;

use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Services\Sms\Exceptions\SmsVerifyException;
use App\Repositories\User\UserRepository;
use App\Services\Auth\PassportService;
use App\Services\Sms\SmsVerifyService;
use App\Services\Telegram\TelegramDev;
use App\ValueObjects\Phone;

class SmsVerify extends BaseGraphQL
{
    public function __construct(
        private SmsVerifyService $smsVerifyService,
        private PassportService $passportService,
        private UserRepository $userRepository
    )
    {}

    /**
     * Return information about current user
     *
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return array
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
        try {
            $phone = $this->getPhoneFromArgs($args);

            $verifyModel = $this->smsVerifyService->create($phone);

            TelegramDev::info("SMS code: {$verifyModel->sms_code}", $args['phone'] ?? '', TelegramDev::LEVEL_IMPORTANT);

            return [
                'status' => true,
                'smsToken' => $verifyModel->sms_token->getValue(),
                'message' => '',
                'smsCode' => config('sms.enable_sender') ? '' : $verifyModel->sms_code
            ];

        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }

    private function getPhoneFromArgs(array $args): Phone
    {
        if(isset($args['phone']) && !empty($args['phone'])){
            return new Phone($args['phone']);
        }

        if(isset($args['accessToken']) && !empty($args['accessToken'])){
            $user_id = $this->passportService->getUserIdByAccessToken($args['accessToken']);
            $user = $this->userRepository->findByID($user_id, [], false, __('error.not found user'));

            return $user->phone;
        }

        throw new SmsVerifyException(__('error.sms verify not have required field'), ErrorsCode::SMS_VERIFY_NOT_FIELD);
    }
}
