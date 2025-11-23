<?php

namespace WezomCms\Users\Http\Livewire;

use Auth;
use Hash;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use WezomCms\Core\Foundation\JsResponse;
use WezomCms\Users\Models\User;

/**
 * Class ChangePassword
 * @package WezomCms\Users\Http\Livewire
 * @property User|Authenticatable $user
 */
class ChangePassword extends Component
{
    /**
     * @var string|null
     */
    public $oldPassword;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @var string|null
     */
    public $passwordConfirmation;

    /**
     * @var int
     */
    public $passwordMinLength;

    /**
     * @param  null  $id
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function __construct($id = null)
    {
        Auth::authenticate();

        parent::__construct($id);
    }

    public function mount()
    {
        $this->passwordMinLength = config('cms.users.users.password_min_length');
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        return view('cms-users::site.livewire.change-password');
    }

    /**
     * @throws ValidationException
     */
    public function submit()
    {
        $this->validate(...$this->rules());

        $user = Auth::user();

        if (!password_verify($this->oldPassword, $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'oldPassword' => [__('cms-users::site.cabinet.Password is entered incorrectly')],
            ]);
        }

        $user->update(['password' => Hash::make($this->password)]);

        $this->reset();

        JsResponse::make()
            ->notification(__('cms-users::site.cabinet.Password successfully changed'))
            ->emit($this);
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
     * @return array
     */
    protected function rules(): array
    {
        return [
            [
                'oldPassword' => "required|string|min:{$this->passwordMinLength}|max:255",
                'password' => "required|string|min:{$this->passwordMinLength}|max:255|different:oldPassword",
                'passwordConfirmation' => 'required|string|same:password',
            ],
            [
                'password.different' => __('cms-users::site.cabinet.New password must be different from the old one'),
            ],
            [
                'oldPassword' => __('cms-users::site.cabinet.Old password'),
                'password' => __('cms-users::site.cabinet.New password'),
                'passwordConfirmation' => __('cms-users::site.cabinet.Password confirmation'),
            ]
        ];
    }
}
