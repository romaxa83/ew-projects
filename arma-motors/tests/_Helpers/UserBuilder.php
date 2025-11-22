<?php

namespace Tests\_Helpers;

use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\Notification\Fcm;
use App\Models\User\Car;
use App\Models\User\User;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserBuilder
{
    private string $name = 'test user';
    private string|null $email = 'test@user.com';
    private string $password = 'password';
    private string $phone = '+38099999888877';
    private string $lang = 'ru';
    private bool $phoneVerify = false;
    private bool $emailVerify = false;
    private string|null $uuid = null;
    private $status;
    private $egrpoy;
    private $fcmToken;
    private string|null $new_phone = null;
    private bool $withNotifications = false;

    private bool $softDeleted = false;
    private bool $withRandomCar = false;

    public function softDeleted(): self
    {
        $this->softDeleted = true;

        return $this;
    }
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setLang($lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function setEgrpoy(string $egrpoy)
    {
        $this->egrpoy = $egrpoy;
        return $this;
    }

    public function setFcmToken(string $fcmToken)
    {
        $this->fcmToken = $fcmToken;
        return $this;
    }

    public function setUuid(string $uuid)
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function setNewPhone($phone)
    {
        $this->new_phone = $phone;
        return $this;
    }

    public function getEmail()
    {
        return null != $this->email ? new Email($this->email) : null;
    }

    public function getPhone()
    {
        return new Phone($this->phone);
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getNewPhone()
    {
        return new Phone($this->new_phone);
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getEgrpoy()
    {
        return $this->egrpoy;
    }
    public function getFcmToken()
    {
        return $this->fcmToken;
    }

    public function phoneVerify(): self
    {
        $this->phoneVerify = true;

        return $this;
    }

    public function emailVerify(): self
    {
        $this->emailVerify = true;

        return $this;
    }

    public function withNotifications(): self
    {
        $this->withNotifications = true;

        return $this;
    }

    public function withRandomCar(): self
    {
        $this->withRandomCar = true;

        return $this;
    }

    public function create()
    {
        $user = $this->save();

        if($this->withNotifications){
           $this->createFcmNotification($user);
        }

        if($this->withRandomCar){
            $this->createRandomCar($user);
        }

//        $this->clear();

        return $user;
    }

    private function save()
    {
        $data = [
            'name' => $this->getName(),
            'phone' => $this->getPhone(),
            'phone_verify' => $this->phoneVerify,
            'email_verify' => $this->emailVerify,
            'password' => \Hash::make($this->password),
            'uuid' => $this->getUuid(),
            'lang' => $this->getLang(),
        ];

        if($this->phoneVerify){
            $data['status'] = User::ACTIVE;
        }

        if(null != $this->email){
            $data['email'] = $this->getEmail();
        }
        if(null != $this->status){
            $data['status'] = $this->getStatus();
        }
        if(null != $this->fcmToken){
            $data['fcm_token'] = $this->getFcmToken();
        }
        if(null != $this->egrpoy){
            $data['egrpoy'] = $this->getEgrpoy();
        }

        if(null != $this->new_phone){
            $data['new_phone'] = $this->getNewPhone();
        }

        if($this->softDeleted){
            $data['deleted_at'] = Carbon::now();
        }

        return User::factory()->new($data)->create();
    }

    private function createFcmNotification(User $user)
    {
        Fcm::factory()->count(3)->send()->create(['user_id' => $user->id]);
        Fcm::factory()->count(1)->create(['user_id' => $user->id]);
        Fcm::factory()->count(1)->hasError()->create(['user_id' => $user->id]);
    }

    private function createRandomCar(User $user)
    {
        $brand = Brand::orderBy(\DB::raw('RAND()'))->first();
        $model = Model::orderBy(\DB::raw('RAND()'))->first();

        Car::factory()->create([
            'user_id' => $user->id,
            'brand_id' => $brand->id,
            'model_id' => $model->id
        ]);
    }
}

