<?php

namespace App\GraphQL\Mutations\User\Auth;

use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Repositories\User\UserRepository;
use App\Services\Auth\Exception\MobileTokenException;
use App\Services\Auth\MobileToken;
use App\Services\Auth\UserPassportService;
use App\Services\Telegram\TelegramDev;
use Firebase\JWT\JWT;
use GraphQL\Error\Error;
use Laravel\Passport\Exceptions\OAuthServerException;

class UserRefreshToken extends BaseGraphQL
{
    public function __construct(
        protected UserPassportService $passportService,
        protected UserRepository $userRepository
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return array
     */
    public function __invoke($_, array $args)
    {
        try {
            $mobileToken = $args['mobileToken'];
            $user = $this->userRepository->findByID($args['id']);

            $tokenObj = MobileToken::decode($mobileToken, $user->salt);

            $tokenObj->assetDeviceId($user->device_id);
            $tokens =  arrayKeyToCamel($this->passportService->refreshToken($tokenObj->getRefreshToken()));

            if (isset($tokens['error'])) {
                throw new MobileTokenException(
                    $tokens['errorDescription'],
                    ErrorsCode::MOBILE_TOKEN_PROBLEM_WITH_GENERATE_REFRESH_TOKEN
                );
            }

            // @todo dev-telegram
            TelegramDev::info('Обновлен refreshToken', $user->name);

            return $tokens;
        } catch (MobileTokenException $e){
            TelegramDev::error(__FILE__, $e, null, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        } catch (OAuthServerException $e){
            TelegramDev::error(__FILE__, $e, null, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e, ErrorsCode::MOBILE_TOKEN_PROBLEM_WITH_GENERATE_REFRESH_TOKEN);
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e, ErrorsCode:: MOBILE_TOKEN_SOMETHING_WRONG);
        }
    }

//    public function gen()
//    {
//        $salt = "0GZJSsfaMwwKUW5IdjPR";
//        $deviceId = '2dc69d5207a388fd';
//        $refreshToken = "def502009600059e1c4f28cb631081b7daf5b1b72154c5751bf13e4d99aad95f5c9ed40732e68a35692259cd11124fd59b95fead7c208be4a2cfdd4312bb3156a794b725c00a878d6b7ddb860ef68ed375aeffb24726150d8e54fa787647d44bf3bce3b1883a919acc70529e468e2322aa4c39563f3c2f9d3483a518080db80cc2e12b0805820341edea27dcc538a4545b4f4b505895eb65f19d0004856da4935ad3f4f09782176b9509dbb230b3e248d8f414908b53d10675720d650739d1c93a2f9ac6235e5110baad9ba8e958ce341237da68f69c38ac62e7e13103385c7b40fcba48b0d6808d66d56cc1c5abf330ee2e9708291863dba212a5988e7a36c61d9e9230d1f478e2cbd2cc9483c7dccec2dc6667163d92c9e7deb94c5aaf79067178205046b4ea8b7a15c510bd07482f14c6de389c50ce29c8c6b593d61671a63d03391b8dd51e5e40bdf283e6337638121b74fb5e5955cd17644d333553e231ad";
//
//        $payload = [
//            'deviceId' => $deviceId,
//            'refreshToken' => $refreshToken,
//        ];
//
//        return JWT::encode($payload, $salt);
//    }
}
