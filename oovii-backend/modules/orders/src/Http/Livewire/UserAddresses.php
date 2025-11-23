<?php

namespace WezomCms\Orders\Http\Livewire;

use Auth;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use WezomCms\Core\Foundation\JsResponse;
use WezomCms\Orders\Models\UserAddress;

/**
 * Class EditAddresses
 * @package WezomCms\Users\Http\Livewire
 * @property Collection|UserAddress[] $addresses
 * @property UserAddress $editedAddress
 */
class UserAddresses extends Component
{
    /**
     * @var bool
     */
    public $showEditForm = false;

    /**
     * @var bool
     */
    public $showCreateForm = false;

    /**
     * Id of the address that is currently being edited.
     *
     * @var int|null
     */
    public $editedAddressId;

    /**
     * Array with fields of the editable address.
     *
     * @var array
     */
    public $editedRow = [
        'city' => null,
        'street' => null,
        'house' => null,
        'room' => null,
    ];

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
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function render()
    {
        return view('cms-orders::site.livewire.user-addresses', ['addresses' => $this->addresses]);
    }

    /**
     * Cancel editing and hide editing fields.
     */
    public function cancel()
    {
        $this->showEditForm = false;

        $this->reset('editedRow');
        $this->editedAddressId = null;
        $this->forgetComputed('editedAddress');

        $this->showCreateForm = false;
    }

    /**
     * @throws \Throwable
     */
    public function save()
    {
        $this->resetErrorBag();

        if (!$this->showEditForm && !$this->showCreateForm) {
            return;
        }

        $this->validate(...$this->rules());

        \DB::transaction(function () {
            if ($this->showEditForm) {
                $this->editedAddress->update($this->editedRow);
            } elseif ($this->showCreateForm) {
                /** @var UserAddress $newAddress */
                $newAddress = Auth::user()
                    ->addresses()
                    ->make($this->editedRow);

                if ($this->addresses->isEmpty()) {
                    $newAddress->primary = true;
                }

                $newAddress->save();
            }
        });

        $this->resetErrorBag();

        // Update fields state.
        $this->forgetComputed('addresses');

        $this->cancel();

        JsResponse::make()
            ->notification(__('cms-orders::site.cabinet.Data successfully updated'))
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
     * @param  int  $id
     */
    public function startEditAddress(int $id)
    {
        $this->showEditForm = true;

        $address = $this->addresses->where('id', $id)->first();
        if (!$address) {
            return;
        }

        $this->editedAddressId = $address->id;

        $this->computedPropertyCache['editedAddress'] = $address;

        $this->editedRow = $address->only('city', 'street', 'house', 'room');
    }

    /**
     * @param  int  $id
     */
    public function deleteAddress(int $id)
    {
        $this->addresses->firstWhere('id', $id)->delete();

        $this->forgetComputed('addresses');

        if ($this->addresses->isNotEmpty() && ! $this->addresses->contains('primary', true)) {
            $this->addresses->first()->update(['primary' => true]);
        }
    }

    public function openCreateAddressForm()
    {
        if ($this->showCreateForm === false) {
            $this->resetCreateAddressFields();
        }

        $this->showCreateForm = true;
    }

    /**
     * @param  int  $id
     */
    public function setPrimary(int $id)
    {
        $address = $this->addresses->where('id', $id)->first();
        if (!$address) {
            return;
        }

        // Set primary and reset in another addresses
        if ($address->primary === false) {
            $address->primary = true;
            $this->addresses->where('id', '!=', $address->id)->each(function (UserAddress $userAddress) {
                $userAddress->update(['primary' => false]);
            });
        }

        $address->save();
    }

    public function resetCreateAddressFields()
    {
        $this->reset('editedRow');
    }

    /**
     * @return Collection|UserAddress[]
     */
    public function getAddressesProperty()
    {
        return Auth::user()
            ->addresses()
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return UserAddress|null
     */
    public function getEditedAddressProperty(): ?UserAddress
    {
        return $this->editedAddressId ? $this->addresses->where('id', $this->editedAddressId)->first() : null;
    }

    /**
     * @return array[]
     */
    protected function rules(): array
    {
        return [
            [
                'editedRow.city' => 'required|string|max:255',
                'editedRow.street' => 'required|string|max:50',
                'editedRow.house' => 'required|string|max:10',
                'editedRow.room' => 'nullable|int|min:1',
            ],
            [],
            [
                'editedRow.city' => __('cms-orders::site.cabinet.City'),
                'editedRow.street' => __('cms-orders::site.cabinet.Street'),
                'editedRow.house' => __('cms-orders::site.cabinet.House'),
                'editedRow.room' => __('cms-orders::site.cabinet.Room'),
            ]
        ];
    }
}
