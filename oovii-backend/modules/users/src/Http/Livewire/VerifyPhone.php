<?php

namespace WezomCms\Users\Http\Livewire;

use Flash;
use Illuminate\Auth\Events\Verified;
use Illuminate\Validation\Rule;
use Livewire\Component;
use WezomCms\Users\Models\User;

/**
 * Class VerifyPhone
 * @package WezomCms\Users\Http\Livewire
 * @property-read User $user
 */
class VerifyPhone extends Component
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
     * @param  User  $user
     */
    public function mount(User $user)
    {
        $this->userId = $user->id;
        $this->computedPropertyCache['user'] = $user;
    }

    public function render()
    {
        return view('cms-users::site.livewire.verify-phone');
    }

    public function submit()
    {
        $this->resetErrorBag();

        $this->validate(...$this->rules());

        if ($this->user->markEmailAsVerified()) {
            event(new Verified($this->user));
        }

        Flash::success(__('cms-users::site.auth.You have successfully confirmed your phone'));

        $this->redirectRoute('cabinet');
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
            ],
            [],
            [
                'code' => __('cms-users::site.cabinet.Code'),
            ]
        ];
    }

    /**
     * @return User
     */
    public function getUserProperty()
    {
        return User::findOrFail($this->userId);
    }
}
