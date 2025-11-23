<?php

namespace WezomCms\Users\Http\Livewire;

use Auth;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Livewire\Component;
use WezomCms\Core\Http\Controllers\ThrottlesLogins;
use WezomCms\Users\Models\User;
use WezomCms\Users\Rules\EmailOrPhone;

class Login extends Component
{
    use ThrottlesLogins;

    /**
     * @var string
     */
    public $login;

    /**
     * @var string
     */
    public $password;

    /**
     * @var bool
     */
    public $remember = true;

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
        return view('cms-users::site.livewire.login');
    }

    /**
     * @param  Request  $request
     */
    public function submit(Request $request)
    {
        if ($this->guard()->check()) {
            $this->redirect($this->redirect);

            return;
        }

        $this->validate(...$this->rules());

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $this->sendLockoutResponse($request);

            return;
        }

        if ($this->attemptLogin()) {
            $request->session()->regenerate();

            $this->clearLoginAttempts($request);

            \Flash::success(__('cms-users::site.auth.You are successfully logged in'));

            $this->redirect($this->redirect);

            return;
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        $this->addError('login', __('cms-users::site.auth.failed'));
    }

    /**
     * @return array
     */
    protected function rules(): array
    {
        return [
            [
                'login' => ['required', 'string', new EmailOrPhone()],
                'password' => [
                    'required',
                    'string',
                    'min:' . config('cms.users.users.password_min_length'),
                    'max:255',
                ],
                'remember' => 'nullable|boolean'
            ],
            [],
            [
                'login' => __('cms-users::site.cabinet.E-mail or Phone'),
                'password' => __('cms-users::site.cabinet.Password'),
                'remember' => __('cms-users::site.cabinet.Remember me'),
            ]
        ];
    }

    /**
     * Attempt to log the user into the application.
     *
     * @return bool
     */
    protected function attemptLogin()
    {
        return $this->guard()->attempt(
            [
                $this->username() => $this->login,
                'password' => $this->password,
                'active' => true,
            ],
            $this->remember
        );
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  Request  $request
     * @return void
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        $this->addError('login', __('cms-users::site.auth.throttle', ['seconds' => $seconds]));
    }

    /**
     * Get the login username to be used by the component.
     *
     * @return string
     */
    protected function username()
    {
        return User::emailOrPhone($this->login);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
