<?php

namespace WezomCms\Core\Http\Controllers\Auth;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Lang;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Http\Controllers\RedirectsUsers;
use WezomCms\Core\Http\Controllers\ThrottlesLogins;
use WezomCms\Core\Http\Requests\Auth\LoginRequest;

class LoginController extends AdminController
{
    use RedirectsUsers;
    use ThrottlesLogins;

    /**
     * Show the application's login form.
     *
     * @return View
     * @throws BindingResolutionException
     */
    public function showLoginForm()
    {
        $this->pageName->setPageName(__('cms-core::admin.auth.Login'));

        $this->renderJsValidator(LoginRequest::class);

        return view('cms-core::admin.auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  LoginRequest  $request
     * @return JsonResponse|RedirectResponse|Response|void
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request)
    {
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $this->sendLockoutResponse($request);
            return;
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            [
                $this->username() => $request->get($this->username()),
                'password' => $request->get('password'),
                'active' => true,
            ],
            $request->filled('remember')
        );
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user());
    }

    /**
     * @param  Request  $request
     * @param $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated(Request $request, $user)
    {
        $intended = redirect()->intended(route('admin.dashboard'));

        $prefix = '/' . config('app.admin_prefix', 'wezom');

        return \Str::is([$prefix, $prefix . '/*'], parse_url($intended->getTargetUrl(), PHP_URL_PATH))
            ? $intended
            : redirect()->route('admin.dashboard');
    }

    /**
     * Get the failed login response instance.
     *
     * @param  Request  $request
     *
     * @throws ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [__('cms-core::admin.auth.failed')],
        ]);
    }
    /**
     * Log the user out of the application.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function logout(Request $request)
    {
        $guard = $this->guard();

        $guard->logout();

        $request->session()->forget($guard->getName());

        return redirect()->route('admin.login-form');
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  Request  $request
     * @return void
     *
     * @throws ValidationException
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        throw ValidationException::withMessages([
            $this->username() => [Lang::get('cms-core::admin.auth.throttle', ['seconds' => $seconds])],
        ])->status(429);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * @inheritDoc
     */
    public function username()
    {
        return 'email';
    }
}
