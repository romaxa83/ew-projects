<?php

namespace App\Services;

use App\DTO\JD\UserDTO;
use App\Models\User\IosLink;
use App\Models\User\Profile;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\JD\DealersRepository;
use App\Repositories\IosLinkRepository;
use App\Repositories\User\RoleRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected RoleRepository $roleRepository,
        protected DealersRepository $dealersRepository,
        protected IosLinkRepository $iosLinkRepository
    )
    {}

    public function createFromImport(UserDTO $dto, string $password) : User
    {
        DB::beginTransaction();
        try {
            $user = new User();
            $user->login = $dto->login;
            $user->email = $dto->email;
            $user->phone = $dto->mobileNumber;
            $user->status = $dto->status;
            $user->jd_id = $dto->jdID;
            $user->password = Hash::make($password);
            $user->nationality_id = $dto->countryID;

            if($dealer = $this->dealersRepository->getBy('jd_id', $dto->dealerID)){
                $user->dealer_id = $dealer->id;
            }

            if($dto->reallyDealerID){
                $user->dealer_id = $dto->reallyDealerID;
            }
            if($dto->lang){
                $user->lang = $dto->lang;
            }

            $user->save();

            $profile = new Profile();
            $profile->first_name = $dto->firstName;
            $profile->last_name = $dto->lastName;

            $user->profile()->save($profile);

            // привязка импортированых дилеров к tm
            foreach($dto->dealerIDs as $dealerId){
                $dealer = $this->dealersRepository->getBy('jd_id', $dealerId);
                $user->dealers()->attach($dealer);
            }

            DB::commit();

            return $user->refresh();
        } catch (\Exception $exception){
            DB::rollBack();
            \Log::error($exception->getMessage());
            throw new \Exception($exception->getMessage());
        }
    }

    public function updateFromImport(User $user, UserDTO $dto) : User
    {
        $user->login = $dto->login;
        $user->email = $dto->email;
        $user->phone = $dto->mobileNumber;
        $user->status = $dto->status;

        if($dealer = $this->dealersRepository->getBy('jd_id', $dto->dealerID)){
            $user->dealer_id = $dealer->id;
        }

        $user->profile->first_name = $dto->firstName;
        $user->profile->last_name = $dto->lastName;

        DB::transaction(function() use($user, $dto) {
            $user->push();

            // привязка импортированых дилеров к tm
            // отвязываем старые связи
            if($dealers = $user->dealers){
                $dealers->each(function($dealer) use ($user){
                    $user->dealers()->detach($dealer);
                });
            }

            foreach($dto->dealerIDs as $dealerId){
                $dealer = $this->dealersRepository->getBy('jd_id', $dealerId);
                $user->dealers()->attach($dealer);
            }
        });

        return $user;
    }

    public function create(UserDTO $dto) : User
    {
        $model = new User();
        $model->login = $dto->login;
        $model->email = $dto->email;
        $model->phone = $dto->mobileNumber;
        $model->nationality_id = $dto->countryID;
        $model->password = Hash::make($dto->password);
        $model->status = User::STATUS_ACTIVE;
        if($dto->role == Role::ROLE_PS){
            $model->dealer_id = $dto->dealerID;
        }
        $model->save();

        $profile = new Profile();
        $profile->first_name = $dto->firstName;
        $profile->last_name = $dto->lastName;

        $model->profile()->save($profile);

        if($dto->role){
            $role = $this->roleRepository->findBy('role', $dto->role);
            $model->roles()->attach($role);
        }

        if(!empty($dto->egIDs) && $model->isPSS()){
            $model->egs()->attach($dto->egIDs);
        }

        if(!empty($dto->dealerIDs) && $model->isTMD()){
            $model->dealers()->attach($dto->dealerIDs);
        }

        $model = $this->addIosLink($model);

        return $model;
    }

    public function edit(User $model, UserDTO $dto): User
    {

        $model->login = $dto->login ?? $model->login;
        $model->email = $dto->email ?? $model->email;
        $model->phone = $dto->mobileNumber ?? $model->phone;
        $model->dealer_id = $dto->dealerID ?? $model->dealer_id;
        $model->nationality_id = $dto->countryID ?? $model->nationality_id;
        $model->save();

        $model->profile->first_name = $dto->firstName ?? $model->profile->first_name;
        $model->profile->last_name = $dto->lastName ?? $model->profile->last_name;
        $model->profile()->save($model->profile);

        if(!empty($dto->egIDs) && $model->isPSS()){
            $model->egs()->detach();
            $model->egs()->attach($dto->egIDs);
        }

        if(!empty($dto->dealerIDs) && $model->isTMD()){
            $model->dealers()->detach();
            $model->dealers()->attach($dto->dealerIDs);
        }

        return $model->refresh();
    }

    public function attachEgs(array $data, User $user): User
    {
        if(!$user->isPSS()){
            throw new \Exception(__('message.only pss', ['role' => $user->getRole()]));
        }
        $user->egs()->detach();
        if(!empty($data)){
            $user->egs()->attach($data);
        }

        return $user->refresh();
    }

    public function changeStatus(User $user, $status): User
    {
        $user->status = $status;
        $user->save();

        return $user;
    }

    public function setFcmToken(User $user, $token): User
    {
        $user->fcm_token = $token;
        $user->save();

        return $user;
    }

    public function changeLanguage(User $user, $lang): User
    {
        $user->lang = $lang;
        $user->save();

        return $user;
    }

    public function changePassword(User $user, $password): User
    {
        $user->password = Hash::make($password);
        $user->save();

        return $user;
    }

    public function savePassword(User $user, string $password): User
    {
        $user->password = Hash::make($password);
        $user->save();

        return $user;
    }

    public function addIosLink(User $user): User
    {
        /** @var IosLink $iosLink */
        $iosLink = $this->iosLinkRepository->getBy('status', 1);
        if(null == $iosLink){
            throw new \Exception(__('message.not empty ios link'),Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->ios_link = $iosLink->link;
        $user->save();

        $iosLink->status = 0;
        $iosLink->user_id = $user->id;
        $iosLink->save();

        return $user;
    }
}
