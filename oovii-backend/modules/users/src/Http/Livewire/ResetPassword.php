<?php

namespace WezomCms\Users\Http\Livewire;

use Auth;
use Flash;
use Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Str;
use Lang;
use Livewire\Component;
use Password;
use WezomCms\Users\Models\User;

class ResetPassword extends Component
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @var string|null
     */
    public $passwordConfirmation;

    /**
     * @param string $token
     */
    public function mount(string $token)
    {
        $this->token = $token;
        $this->email = app('request')->get('email');
    }

    public function render()
    {
        return view('cms-users::site.livewire.reset-password')->extends('cms-ui::layouts.main');
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
        if ($field === 'passwordConfirmation') {
            $field = 'password';
        }

        $this->validateOnly($field, ...$this->rules());
    }

    public function submit()
    {
        $this->resetErrorBag();

        $this->validate(...$this->rules());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = Password::broker()
            ->reset(
                $this->credentials(),
                function ($user, $password) {
                    $this->resetPassword($user, $password);
                }
            );

        /**
         * If the password was successfully reset, we will redirect the user back to
         * the application's home authenticated view. If there is an error we can
         * redirect them back to where they came from with their error message.
         */
        if ($response == Password::PASSWORD_RESET) {
            Flash::success(Lang::get('cms-users::site.auth.' . $response));

            $this->redirectRoute('cabinet');
        } else {
            $this->addError('email', Lang::get('cms-users::site.auth.' . $response));
        }
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @return array
     */
    protected function credentials(): array
    {
        $credentials = $this->only(['email', 'password', 'token']);
        $credentials['active'] = true;

        return $credentials;
    }

    /**
     * Reset the given user's password.
     *
     * @param  CanResetPassword|User  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        Auth::guard()->login($user);
    }

    /**
     * Get the validation rules.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            [
                'email' => 'required|email',
                'password' => [
                    'required',
                    'string',
                    'min:' . config('cms.users.users.password_min_length'),
                    'max:255',
                    'same:passwordConfirmation'
                ],
                'token' => 'required',
            ],
            [],
            [
                'email' => __('cms-users::site.cabinet.E-mail'),
                'password' => __('cms-users::site.cabinet.New password'),
                'passwordConfirmation' => __('cms-users::site.cabinet.Password confirmation'),
                'token' => __('cms-users::site.auth.Token'),
            ]
        ];
    }
}
