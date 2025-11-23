<?php

namespace WezomCms\SmsVerify\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Log;
use Throwable;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\SmsVerify\Events\SendSmsCode;
use WezomCms\SmsVerify\Exceptions\SmsVerifyException;
use WezomCms\SmsVerify\Http\Requests\Api\V1;
use WezomCms\SmsVerify\Repositories\SmsVerifyRepository;
use WezomCms\SmsVerify\Services\SmsVerifyService;
use WezomCms\Users\Models\User;
use WezomCms\Users\Repositories\UserRepository;
use WezomCms\Users\Services\Auth\PassportService;

class SmsVerifyController extends ApiController
{
    public function __construct(
        protected SmsVerifyService $smsVerifyService,
        protected SmsVerifyRepository $smsVerifyRepository,
        private PassportService $passportService,
        private UserRepository $userRepository,
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Post (
     *     path="/mobile/sms-verify/verify",
     *     tags={"SMS"},
     *
     *     summary="Sms verify request",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/SmsVerifyRequest")),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                   @OA\Property(property="smsToken", title="Sms token", example="7b11027f-1913-411a-b5ec-8878ef3a7c30"),
     *                   @OA\Property(property="smsCode", title="Sms code", example="1913"),
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param V1\SmsVerifyRequest $request
     * @return JsonResponse
     */
    public function verify(V1\SmsVerifyRequest $request): JsonResponse
    {
        try {
            $phone = $this->getPhone($request->all());
            $model = $this->smsVerifyService->create($phone);

            event(new SendSmsCode($model));

            return self::successJsonMessage([
                'smsToken' => $model->sms_token->getValue(),
                'smsCode' => config('cms.sms-verify.config.sender.enable') ? null : $model->code
            ]);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/sms-verify/check",
     *     tags={"SMS"},
     *
     *     summary="Check sms",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/SmsCheckRequest")),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                   @OA\Property(property="actionToken", title="Action token", example="7b11027f-1913-411a-b5ec-8878ef3a7c30")
     *              )
     *         ),
     *         @OA\Property(property="success", title="Success", example=true),
     *     ),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param V1\SmsCheckRequest $request
     * @return JsonResponse
     */
    public function check(V1\SmsCheckRequest $request): JsonResponse
    {
        try {
            $model = $this->smsVerifyRepository->findBySmsToken($request['smsToken']);

            if ($model->sms_token->isExpiredToNow()) {
                SmsVerifyException::throwExpiredSmsToken($request['smsToken']);
            }

            if (!$model->equalsCode($request['code'])) {
                SmsVerifyException::throwNotEqualSmsCode();
            }

            $model = $this->smsVerifyService->setActionToken($model);

            return self::successJsonMessage([
                'actionToken' => $model->action_token->getValue(),
            ]);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param array $data
     * @return string
     * @throws SmsVerifyException
     */
    private function getPhone(array $data): string
    {
        if(isset($data['phone']) && !empty($data['phone'])){
            return $data['phone'];
        }

        if(isset($data['accessToken']) && !empty($data['accessToken'])){
            $user_id = $this->passportService->getUserIdByAccessToken($data['accessToken']);
            /** @var User $user */
            $user = $this->userRepository->findByID($user_id, [], false, 'not found user');

            return $user->phone;
        }

        throw new SmsVerifyException("sms verify not have required field", Response::HTTP_BAD_REQUEST);
    }

}

