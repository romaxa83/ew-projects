<?php

namespace WezomCms\Users\Http\Controllers\Site\Auth;

use Auth;
use Exception;
use Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Socialite;
use WezomCms\Core\Http\Controllers\SiteController;
use WezomCms\Users\Models\SocialAccount;
use WezomCms\Users\Services\SocialAccountService;

class SocialAuthController extends SiteController
{
    /**
     * @param $driver
     * @param  Request  $request
     * @return mixed
     */
    public function redirect($driver, Request $request)
    {
        $this->checkDriver($driver);

        if ($redirectPath = $request->get('redirect')) {
            redirect()->setIntendedUrl($redirectPath);
        }

        try {
            $socialite = Socialite::driver($driver);
            if ($fields = config("cms.users.users.socials.{$driver}.fields", [])) {
                $socialite->fields($fields);
            }
            if ($scopes = config("cms.users.users.socials.{$driver}.scopes", [])) {
                $socialite->scopes($scopes);
            }

            return $socialite->redirect();
        } catch (Exception $e) {
            Flash::error(__('cms-users::site.auth.Authorisation Error Please try again'));

            return redirect()->back();
        }
    }

    /**
     * @param $driver
     * @param  SocialAccountService  $service
     * @return RedirectResponse|Redirector
     */
    public function callback($driver, SocialAccountService $service)
    {
        $this->checkDriver($driver);

        try {
            $socialite = Socialite::driver($driver);
            if ($fields = config("cms.users.users.socials.{$driver}.fields", [])) {
                $socialite->fields($fields);
            }
            $remoteUser = $socialite->user();
        } catch (Exception $e) {
            Flash::error(__('cms-users::site.auth.Authorisation Error Please try again'));

            return redirect()->intended(route('home'));
        }

        $authorizedUser = Auth::user();
        if ($authorizedUser) {
            // User want add social.
            $result = $service->connectWithAuthorizedUser($driver, $remoteUser, $authorizedUser);

            if ($result) {
                Flash::success(__('cms-users::site.auth.Social network account added successfully!'));
            } else {
                Flash::warning(__('cms-users::site.auth.This account is already linked to another user of the site!'));
            }

            return redirect()->route('cabinet');
        } else {
            // User authorize.
            $user = $service->createOrGetUser($driver, $remoteUser);

            // If user activated
            if ($user->active) {
                if (!$user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }

                auth()->login($user);

                Flash::success(__('cms-users::site.auth.You are successfully logged in'));

                return redirect()->intended(route('cabinet'));
            } else {
                Flash::error(__('cms-users::site.auth.User is deactivated Please contact the site administration'));

                return redirect()->intended(route('home'));
            }
        }
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @throws Exception
     */
    public function disconnect($id)
    {
        /** @var SocialAccount $row */
        $row = SocialAccount::find($id);

        if ($row) {
            $row->delete();

            Flash::success(__('cms-users::site.auth.Social network account successfully disabled!'));
        }

        return back();
    }

    /**
     * @param $driver
     * @return void
     */
    private function checkDriver($driver)
    {
        if (!in_array($driver, config('cms.users.users.supported_socials'))) {
            abort(404);
        }
    }
}
