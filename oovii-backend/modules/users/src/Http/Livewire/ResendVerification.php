<?php

namespace WezomCms\Users\Http\Livewire;

use Auth;
use Livewire\Component;
use WezomCms\Core\Foundation\JsResponse;
use WezomCms\Users\Models\User;

class ResendVerification extends Component
{
    /**
     * @param  null  $id
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function __construct($id = null)
    {
        Auth::authenticate();

        parent::__construct($id);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('cms-users::site.livewire.resend-verification');
    }

    public function submit()
    {
        $user = Auth::user();
        if ($user->hasVerifiedEmail()) {
            $this->redirect(route('cabinet'));
        }

        $user->sendEmailVerificationNotification();

        if ($user->registered_through === User::PHONE) {
            $message = __('cms-users::site.auth.A fresh verification code has been sent to your phone');
        } else {
            $message = __('cms-users::site.auth.A fresh verification link has been sent to your email address');
        }

        JsResponse::make()
            ->notification($message)
            ->emit($this);
    }
}
