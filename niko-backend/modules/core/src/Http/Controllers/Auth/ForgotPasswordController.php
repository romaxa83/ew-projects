<?php

namespace WezomCms\Core\Http\Controllers\Auth;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Lang;
use Password;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Http\Requests\Auth\SendResetLinkRequest;

class ForgotPasswordController extends AdminController
{
    /**
     * Display the form to request a password reset link.
     *
     * @return View
     * @throws BindingResolutionException
     */
    public function showLinkRequestForm()
    {
        $this->pageName->setPageName(__('cms-core::admin.auth.Reset password'));

        $this->renderJsValidator(SendResetLinkRequest::class);

        return view('cms-core::admin.auth.passwords.email');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  SendResetLinkRequest  $request
     * @return RedirectResponse|JsonResponse
     */
    public function sendResetLinkEmail(SendResetLinkRequest $request)
    {
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = Password::broker('admins')->sendResetLink([
            'email' => $request->get('email'),
            'active' => true,
        ]);

        if ($response === Password::RESET_LINK_SENT) {
            return back()->with('status', Lang::get('cms-core::admin.auth.' . $response));
        } else {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => Lang::get('cms-core::admin.auth.' . $response)]);
        }
    }
}
