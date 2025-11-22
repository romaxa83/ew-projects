<?php

namespace App\Http\Controllers\V1\Auth;

use App\Events\ModelChanged;
use App\Http\Requests\AuthRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\AuthResource;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Services\Passport\UserPassportService;
use Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class AuthController extends \App\Http\Controllers\Api\AuthController
{
    /**
     * @param AuthRequest $request
     * @param UserPassportService $authService
     * @return LoginResource|JsonResponse
     * @throws Throwable
     */
    public function login(AuthRequest $request, UserPassportService $authService)
    {
        $user = User::whereEmail($request->email)->first();

        if (!$user || !$user->exists) {
            return $this->makeErrorResponse(trans('auth.empty_login'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$user->isActive()) {
            return $this->makeErrorResponse(trans('auth.user_deactivated'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ((!$user->getCompany() || !$user->getCompany()->isActive()) && !$user->isBodyShopUser()) {
            return $this->makeErrorResponse(trans('auth.company_not_active'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (Config::get('mobile') === false && $user->isDriver() && !$user->isOwner()) {
            return $this->makeErrorResponse(trans('auth.failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $auth = $authService->auth($request->email, $request->password);

        if (isset($auth['error'])) {
            return $this->makeErrorResponse(trans('auth.failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if($company = $user->getCompany()){
            // если юзер зашел в систему после добавления карточки, проставляем чекер чтоб ему не приходили письма по уведомлениям,
            // т.к. если пользователь апрувнул почту и добавил карточку а после не заходит в систему, то ему приходят письма приглашающие в систему
            /** @var $company Company */
            if(
                $company->isFreePlan()
                && $company->paymentMethod
                && $company->not_login_free_trial_count != 3
            ){
                $company->update(['not_login_free_trial_count' => 3]);
            }
        }

        event(
            new ModelChanged(
                $user,
                'history.user_logged_in',
                [
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                ]
            )
        );

        return LoginResource::make(array_merge($auth, ['user' => $user]));
    }

    /**
     * @param AuthRequest $request
     * @param UserPassportService $authService
     * @return AuthResource|JsonResponse
     * @throws Throwable
     */
    public function driverLogin(AuthRequest $request, UserPassportService $authService)
    {
        $user = User::whereEmail($request->email)->first();

        if (!$user || !$user->exists) {
            return $this->makeErrorResponse(trans('auth.empty_login'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$user->isActive()) {
            return $this->makeErrorResponse(trans('auth.user_deactivated'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$user->getCompany() || !$user->getCompany()->isActive()) {
            return $this->makeErrorResponse(trans('auth.company_not_active'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (Config::get('mobile') === false && $user->isDriver()) {
            return $this->makeErrorResponse(trans('auth.failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $auth = $authService->auth($request->email, $request->password);

        if (isset($auth['error'])) {
            return $this->makeErrorResponse(trans('auth.failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        event(
            new ModelChanged(
                $user,
                'history.user_logged_in',
                [
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                ]
            )
        );

        return AuthResource::make($auth);
    }
}
