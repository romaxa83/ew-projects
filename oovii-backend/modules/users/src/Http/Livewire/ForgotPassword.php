<?php

namespace WezomCms\Users\Http\Livewire;

use Illuminate\Contracts\Auth\PasswordBroker;
use Lang;
use Livewire\Component;
use Password;
use WezomCms\Core\Foundation\JsResponse;
use WezomCms\Users\Models\User;
use WezomCms\Users\Rules\EmailOrPhone;

class ForgotPassword extends Component
{
    /**
     * @var string
     */
    public $login;

    /**
     * @var string
     */
    public $redirect;

    public function mount()
    {
        $this->redirect = request()->get('redirect', route('cabinet'));
    }

    /**
     * Validate only updated field.
     *
     * @param $field
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updated($field)
    {
        $this->validateOnly($field, ...$this->rules());
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        return view('cms-users::site.livewire.forgot-password');
    }

    public function submit()
    {
        $this->resetErrorBag();

        $this->validate(...$this->rules());

        if (User::emailOrPhone($this->login) === User::EMAIL) {
            $response = $this->sendResetLink();
        } else {
            $response = $this->sendResetCode();
        }

        if ($response) {
            $this->addError('email', Lang::get('cms-users::site.auth.' . $response));
        }
    }

    /**
     * @return string|null
     */
    protected function sendResetLink(): ?string
    {
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink([
            'active' => true,
            'email' => $this->login,
        ]);

        if ($response == Password::RESET_LINK_SENT) {
            JsResponse::make()
                ->notification(__('cms-users::site.auth.To the specified email sent a link to reset your password'))
                ->modal('close')
                ->emit($this);

            return null;
        }

        return $response;
    }

    /**
     * @return string|null
     */
    protected function sendResetCode(): ?string
    {
        /** @var User $user */
        $user = $this->broker()->getUser([
            'active' => true,
            'phone' => $this->login,
        ]);

        if (is_null($user)) {
            $response = __('cms-users::site.auth.User not found');
        } else {
            $response = $user->sendPasswordResetByCodeNotification()
                ? null
                : __('cms-users::site.auth.An error occurred while sending a message');
        }

        if (!$response) {
            JsResponse::make()
                ->notification(__('cms-users::site.auth.To the specified phone sent a code to reset your password'))
                ->modal(['component' => ['name' => 'users.reset-password-by-code', 'params' => ['id' => $user->id]]])
                ->emit($this);

            return null;
        }

        return $response;
    }

    /**
     * @return array
     */
    protected function rules(): array
    {
        return [
            ['login' => ['required', 'string', new EmailOrPhone()]],
            [],
            ['login' => __('cms-users::site.cabinet.E-mail or phone')]
        ];
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return PasswordBroker
     */
    protected function broker()
    {
        return Password::broker();
    }
}
