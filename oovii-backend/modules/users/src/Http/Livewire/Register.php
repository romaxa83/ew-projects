<?php

namespace WezomCms\Users\Http\Livewire;

use Auth;
use Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Services\CheckForSpam;
use WezomCms\Newsletter\NewsletterServiceProvider;
use WezomCms\Newsletter\Services\UserSubscription;
use WezomCms\Users\Models\User;
use WezomCms\Users\Rules\EmailOrPhone;

/**
 * Class Register
 * @package WezomCms\Users\Http\Livewire
 * @property bool $newsletterLoaded
 */
class Register extends Component
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $surname;

    /**
     * @var string
     */
    public $login;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $passwordConfirmation;

    /**
     * @var string
     */
    public $redirect;

    /**
     * @var bool
     */
    public $subscribe;

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
        if ($field === 'passwordConfirmation') {
            $field = 'password';
        }

        $this->validateOnly($field, ...$this->rules());
    }

    /**
     * @return Factory|View
     */
    public function render()
    {
        return view('cms-users::site.livewire.register');
    }

    /**
     * Form submit handler
     * @param  CheckForSpam  $checkForSpam
     */
    public function submit(CheckForSpam $checkForSpam)
    {
        if (!$checkForSpam->checkInComponent($this)) {
            return;
        }

        if ($this->guard()->check()) {
            $this->redirect(route('cabinet'));

            return;
        }

        $user = $this->create($this->validate(...$this->rules()));

        $this->guard()->login($user);

        event(new Registered($user));

        $this->redirect($this->redirect);
    }

    /**
     * @return bool
     */
    public function getNewsletterLoadedProperty(): bool
    {
        return Helpers::providerLoaded(NewsletterServiceProvider::class);
    }

    /**
     * @return array
     */
    protected function rules(): array
    {
        return [
            [
                'name' => ['required', 'string', 'max:255'],
                'surname' => ['required', 'string', 'max:255'],
                'login' => ['required', 'string', new EmailOrPhone(true)],
                'password' => [
                    'required',
                    'string',
                    'min:' . config('cms.users.users.password_min_length'),
                    'max:255',
                    'same:passwordConfirmation'
                ],
                'subscribe' => 'nullable|in:1',
            ],
            [],
            [
                'name' => __('cms-users::site.cabinet.Name'),
                'surname' => __('cms-users::site.cabinet.Surname'),
                'login' => __('cms-users::site.cabinet.Login'),
                'password' => __('cms-users::site.cabinet.Password'),
                'passwordConfirmation' => __('cms-users::site.cabinet.Password confirmation'),
                'subscribe' => __('cms-users::site.cabinet.Subscribe'),
            ]
        ];
    }

    /**
     * Create a new user instance.
     *
     * @param array $data
     * @return User
     */
    protected function create(array $data)
    {
        $userData = array_except($data, ['login', 'password', 'passwordConfirmation', 'subscribe']);

        $login = array_get($data, 'login');
        $loginField = User::emailOrPhone($login);

        $userData[$loginField] = $login;
        $userData['password'] = Hash::make($data['password']);
        $userData['registered_through'] = $loginField;
        $userData['active'] = true;

        return tap(User::create($userData), function (User $user) use ($data) {
            if ($this->newsletterLoaded && $user->registered_through === User::EMAIL) {
                UserSubscription::updateOrCreate($user, (bool) array_get($data, 'subscribe'));
            }
        });
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
