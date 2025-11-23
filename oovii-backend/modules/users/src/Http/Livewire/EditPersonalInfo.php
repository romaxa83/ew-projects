<?php

namespace WezomCms\Users\Http\Livewire;

use Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use WezomCms\Core\Foundation\JsResponse;
use WezomCms\Core\Rules\PhoneMask;

class EditPersonalInfo extends Component
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $surname;

    /**
     * @var string|null
     */
    public $patronymic;

    /**
     * @var string|null
     */
    public $phone;

    /**
     * @var string|null
     */
    public $email;

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
        $this->fillFieldsFromUser();
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('cms-users::site.livewire.edit-personal-info', [
            'user' => Auth::user(),
        ]);
    }

    public function save()
    {
        $this->resetErrorBag();

        $this->validate(...$this->rules());

        Auth::user()->update([
            'name' => $this->name,
            'surname' => $this->surname,
            'patronymic' => $this->patronymic,
            'email' => $this->email,
            'phone' => remove_phone_mask($this->phone),
        ]);

        $this->resetErrorBag();

        JsResponse::make()
            ->notification(__('cms-users::site.cabinet.Data successfully updated'))
            ->emit($this);
    }

    protected function fillFieldsFromUser()
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->surname = $user->surname;
        $this->patronymic = $user->patronymic;
        $this->phone = apply_phone_mask($user->phone);
        $this->email = $user->email;
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
        if (Str::startsWith($field, 'birthday')) {
            return;
        }

        $this->validateOnly($field, ...$this->rules());
    }

    /**
     * @return array[]
     */
    protected function rules(): array
    {
        return [
            [
                'name' => 'required|string|max:191',
                'surname' => 'required|string|max:191',
                'patronymic' => 'required|string|max:191',
                'email' => 'required|string|email|max:255|unique:users,email,' . Auth::user()->id,
                'phone' => ['nullable', new PhoneMask(), 'unique:users,phone,' . Auth::user()->id],
            ],
            [
                'email.unique' => __('cms-users::site.auth.User with provided email already exists'),
                'phone.unique' => __('cms-users::site.auth.User with provided phone already exists'),
            ],
            [
                'name' => __('cms-users::site.cabinet.Name'),
                'surname' => __('cms-users::site.cabinet.Surname'),
                'patronymic' => __('cms-users::site.cabinet.Patronymic'),
                'email' => __('cms-users::site.cabinet.E-mail'),
                'phone' => __('cms-users::site.cabinet.Phone'),
            ]
        ];
    }
}
