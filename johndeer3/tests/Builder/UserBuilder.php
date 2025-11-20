<?php

namespace Tests\Builder;

use App\Models\JD\Dealer;
use App\Models\User\IosLink;
use App\Models\User\Nationality;
use App\Models\User\Profile;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Support\Facades\Hash;

class UserBuilder
{
    private $role;
    private $iosLink;
    private $dealer;
    private $dealerIDs = [];
    private $data = [];
    private $dataProfile = [];
    private $egIDs = [];
    private $withProfile = false;
    private $withCountry = false;

    public function setPassword($value): self
    {
        $this->data['password'] = Hash::make($value);
        return $this;
    }

    public function setLogin($value): self
    {
        $this->data['login'] = $value;
        return $this;
    }

    public function setFcmToken($value): self
    {
        $this->data['fcm_token'] = $value;
        return $this;
    }

    public function setLang($value): self
    {
        $this->data['lang'] = $value;
        return $this;
    }

    public function setEmail($value): self
    {
        $this->data['email'] = $value;
        return $this;
    }

    public function setStatus($value): self
    {
        $this->data['status'] = $value;
        return $this;
    }

    public function setCountry($value): self
    {
        $this->data['nationality_id'] = $value;
        return $this;
    }

    public function setRole(Role $value): self
    {
        $this->role = $value;
        return $this;
    }

    public function setDealer(Dealer $value): self
    {
        $this->dealer = $value;
        return $this;
    }

    public function setIosLink(IosLink $value): self
    {
        $this->iosLink = $value;
        return $this;
    }

    public function setEgIDs(...$value): self
    {
        $this->egIDs = $value;
        return $this;
    }

    public function setDealersIDs(...$value): self
    {
        $this->dealerIDs = $value;
        return $this;
    }

    public function withProfile(): self
    {
        $this->withProfile = true;
        return $this;
    }

    public function profileData(array $value): self
    {
        $this->dataProfile = $value;
        return $this;
    }

    public function withCountry(): self
    {
        $this->withCountry = true;
        return $this;
    }

    public function create()
    {
        if($this->withCountry){
            $country = Nationality::query()->first();
            $this->data['nationality_id'] = $country->id;
        }

        $model = $this->save();

        if(!$this->role){
            $this->role = Role::query()->where('role', Role::ROLE_ADMIN)->first();
        }
        $model->roles()->attach($this->role);

        if($this->role->isPs()){
            if(!$this->dealer){
                $this->dealer = Dealer::query()->first();
            }
            $model->dealer_id = $this->dealer->id;
            $model->save();
        }
        if($this->dealer){
            $model->dealer_id = $this->dealer->id;
            $model->save();
        }

        if($this->withProfile){
            Profile::factory()->create(['user_id' => $model->id]);
        }
        if(!empty($this->dataProfile)){
            $this->dataProfile['user_id'] = $model->id;
            Profile::factory()->create($this->dataProfile);
        }

        if(!empty($this->egIDs)){
            $model->egs()->attach($this->egIDs);
        }
        if(!empty($this->dealerIDs)){
            $model->dealers()->attach($this->dealerIDs);
        }
        if($this->iosLink){
            $this->iosLink->update([
                'user_id' => $model->id,
                'status' => 0,
            ]);
            $model->update(['ios_link' => $this->iosLink->link]);
        }

        $this->clear();

        return $model;
    }

    private function save()
    {
        return User::factory()->new($this->data)->create();
    }

    private function clear(): void
    {
        $this->role = null;
        $this->iosLink = null;
        $this->dealer = null;
        $this->data = [];
        $this->dataProfile = [];
        $this->egIDs = [];
        $this->dealerIDs = [];
        $this->withProfile = false;
        $this->withCountry = false;
    }
}
