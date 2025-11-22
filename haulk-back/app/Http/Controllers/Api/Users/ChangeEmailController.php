<?php

namespace App\Http\Controllers\Api\Users;

use App\Events\ModelChanged;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Users\ChangeEmailRequest;
use App\Http\Resources\Users\ChangeEmailResource;
use App\Models\Users\ChangeEmail;
use App\Models\Users\User;
use App\Notifications\ConfirmNewEmail;
use App\Notifications\EmailChanged;
use App\Notifications\NewEmailCanceled;
use App\Services\Passport\UserPassportService;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Log;
use Throwable;

class ChangeEmailController extends ApiController
{
    public const PUBLIC_CHANGE_EMAIL_PATH = '/email-change';

    /**
     * Store a newly created resource in storage.
     *
     * @param ChangeEmailRequest $request
     * @return ChangeEmailResource|JsonResponse
     * @throws AuthorizationException|Throwable
     *
     * @OA\Post(
     *     path="/api/change-email/",
     *     tags={"Change email"},
     *     summary="Create change email request",
     *     operationId="Create request",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="new_email", in="query", description="New email", required=true,
     *          @OA\Schema(type="string", default="")
     *     ),
     *     @OA\Parameter(name="user_id", in="query", description="User id, if need change another user", required=false,
     *           @OA\Schema(type="integer", example="1")
     *      ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ChangeEmailResource")
     *     ),
     * )
     *
     */
    public function store(ChangeEmailRequest $request)
    {
        $this->authorize('profile update');

        try {
            DB::beginTransaction();

            $confirmToken = Str::random(60);
            $declineToken = Str::random(60);

            $user = $request->user();

            if($request->get('user_id')){
                $user = User::find($request->get('user_id'));
            }

            $model = ChangeEmail::updateOrCreate(
                [
                    'user_id' => $user->id,
                ],
                $request->validated()
            );

            $model->user_id = $user->id;
            $model->old_email = $user->email;
            $model->new_email_confirmed = false;
            $model->confirm_token = hash('sha256', $confirmToken);
            $model->decline_token = hash('sha256', $declineToken);

            $model->saveOrFail();

            $confirmUrl = config('frontend.auth_url') . self::PUBLIC_CHANGE_EMAIL_PATH . '?' . http_build_query([
                'email' => $model->new_email,
                'token' => $confirmToken,
                'type' => 'confirm',
            ]);

            $declineUrl = config('frontend.auth_url') . self::PUBLIC_CHANGE_EMAIL_PATH . '?' . http_build_query([
                'email' => $model->new_email,
                'token' => $declineToken,
                'type' => 'decline',
            ]);

            Notification::route('mail', $model->old_email)->notify(new ConfirmNewEmail($model, null, $declineUrl));
            Notification::route('mail', $model->new_email)->notify(new ConfirmNewEmail($model, $confirmUrl, $declineUrl));

            DB::commit();

            event(new ModelChanged($user, 'history.email_change_created', [
                'full_name' => $user->full_name,
                'old_email' => $model->old_email,
                'new_email' => $model->new_email,
            ]));

            return ChangeEmailResource::make($model);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ChangeEmail $changeEmail
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Delete(
     *     path="/api/change-email/{changeEmailId}",
     *     tags={"Change email"},
     *     summary="Delete change email request",
     *     operationId="Delete request",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     *
     */
    public function destroy(ChangeEmail $changeEmail): JsonResponse
    {
        $this->authorize('profile update');

        $changeEmail->delete();

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Confirm change email request
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     *
     * @OA\Post(
     *     path="/api/change-email/confirm-email",
     *     tags={"Change email"},
     *     summary="Confirm new email",
     *     operationId="Confirm new email",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *
     *     @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="Email to confirm",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default=""
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="token",
     *          in="query",
     *          description="Confirmation token",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default=""
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     * )
     *
     */
    public function confirmEmail(Request $request, UserPassportService $authService): JsonResponse
    {
        $changeEmail = ChangeEmail::where([
            ['new_email', $request->email],
            ['new_email_confirmed', false],
            ['confirm_token', hash('sha256', $request->token)],
        ])->first();

        if (!$changeEmail) {
            return $this->makeErrorResponse(
                'Wrong confirmation email or token.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $user = $changeEmail->user;

            $authService->logout($user);

            $user->tokens->where('revoked', false)->map(function ($token) {
                $token->revoke();
            });

            $changeEmail->user->email = $changeEmail->new_email;
            $changeEmail->user->saveOrFail();

            Notification::route('mail', $changeEmail->old_email)->notify(new EmailChanged($changeEmail));
            Notification::route('mail', $changeEmail->new_email)->notify(new EmailChanged($changeEmail));

//            $user = $changeEmail->user;
//
//            $changeEmail->user->tokens->where('revoked', false)->map(function ($token) {
//                $token->revoke();
//            });

            $changeEmail->delete();

            return $this->makeSuccessResponse(null, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cancel change email request
     *
     * @param  Request  $request
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/api/change-email/cancel-request",
     *     tags={"Change email"},
     *     summary="Cancel change email request",
     *     operationId="Cancel request",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="Email",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default=""
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="token",
     *          in="query",
     *          description="Confirmation token",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default=""
     *          )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     *
     */
    public function cancelRequest(Request $request): JsonResponse
    {
        $changeEmail = ChangeEmail::where([
            ['new_email', $request->email],
            ['new_email_confirmed', false],
            ['decline_token', hash('sha256', $request->token)],
        ])->first();

        if (!$changeEmail) {
            return $this->makeErrorResponse(
                'Wrong confirmation email or token.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            Notification::route('mail', $changeEmail->old_email)->notify(new NewEmailCanceled($changeEmail));

            $changeEmail->delete();

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return ChangeEmailResource|JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/change-email/if-requested",
     *     tags={"Change email"},
     *     summary="Check if current user has change email request",
     *     operationId="Check if current user have request",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="user_id", in="query", description="User id, if need change another user", required=false,
     *         @OA\Schema(type="integer", example="1")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ChangeEmailResource")
     *     ),
     * )
     *
     */
    public function ifRequested(Request $request)
    {
        $this->authorize('profile');

        $user = $request->user();

        if($request->get('user_id')){
            $authUser = $request->user();
            /** @var $authUser User */
            if(!$authUser->isSuperAdmin()){
                return $this->makeErrorResponse(trans('Request not found.'), Response::HTTP_NOT_FOUND);
            }

            $user = User::find($request->get('user_id'));
        }

        $model = ChangeEmail::whereUserId($user->id)->first();

        if (!$model) {
            return $this->makeErrorResponse(trans('Request not found.'), Response::HTTP_NOT_FOUND);
        }

        return ChangeEmailResource::make($model);
    }
}
