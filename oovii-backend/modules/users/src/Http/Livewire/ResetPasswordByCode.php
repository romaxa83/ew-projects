<?php

namespace WezomCms\Users\Http\Livewire;

use Auth;
use Flash;
use Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lang;
use Livewire\Component;
use Password;
use WezomCms\Users\Models\User;

class ResetPasswordByCode extends Component
{
    /**
     * @var int
     */
    public $userId;

    /**
     * @var int|null
     */
    public $code;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @var string|null
     */
    public $passwordConfirmation;

    /**
     * @param  int  $id
     */
    public function mount(int $id)
    {
        $this->userId = $id;
    }

    public function render()
    {
        return view('cms-users::site.livewire.reset-password-by-code');
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

        $this->resetPassword(
            User::whereId($this->userId)->first(),
            $this->password
        );

        Flash::success(Lang::get('cms-users::site.auth.' . Password::PASSWORD_RESET));

        $this->redirectRoute('cabinet');
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

        $user->temporary_code = null;

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
                'code' => [
                    'required',
                    'int',
                    'digits:' . User::TEMPORARY_CODE_LENGTH,
                    Rule::exists('users', 'temporary_code')->where('id', $this->userId)
                ],
                'password' => [
                    'required',
                    'string',
                    'min:' . config('cms.users.users.password_min_length'),
                    'max:255',
                    'same:passwordConfirmation'
                ],
            ],
            [],
            [
                'code' => __('cms-users::site.cabinet.Code'),
                'password' => __('cms-users::site.cabinet.New password'),
                'passwordConfirmation' => __('cms-users::site.cabinet.Password confirmation'),
            ]
        ];
    }
}
