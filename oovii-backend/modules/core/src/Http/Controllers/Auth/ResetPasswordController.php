<?php

namespace WezomCms\Core\Http\Controllers\Auth;

use Auth;
use Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Lang;
use Password;
use Str;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Http\Controllers\RedirectsUsers;
use WezomCms\Core\Http\Requests\Auth\ResetPasswordRequest;
use WezomCms\Core\Models\Administrator;

class ResetPasswordController extends AdminController
{
    use RedirectsUsers;

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  Request  $request
     * @param  string|null  $token
     * @return Factory|View
     * @throws BindingResolutionException
     */
    public function showResetForm(Request $request, $token = null)
    {
        $this->pageName->setPageName(__('cms-core::admin.auth.Reset password'));

        $this->renderJsValidator(ResetPasswordRequest::class);

        return view('cms-core::admin.auth.passwords.reset', ['token' => $token, 'email' => $request->get('email')]);
    }

    /**
     * Reset the given user's password.
     *
     * @param  ResetPasswordRequest  $request
     * @return RedirectResponse|JsonResponse
     */
    public function reset(ResetPasswordRequest $request)
    {
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = Password::broker('admins')
            ->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $this->resetPassword($user, $password);
                }
            );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($response === Password::PASSWORD_RESET) {
            return redirect($this->redirectPath())->with('status', Lang::get('cms-core::admin.auth.' . $response));
        } else {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => Lang::get('cms-core::admin.auth.' . $response)]);
        }
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword|Administrator  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        Auth::guard('admin')->login($user);
    }

    /**
     * Redirect to dashboard after success password reset.
     *
     * @return string
     */
    protected function redirectTo()
    {
        return route('admin.dashboard');
    }
}
