<?php

namespace WezomCms\Users\Services;

use Illuminate\Support\Facades\Hash;
use WezomCms\Requests\ConvertData\ScheduleConvert;
use WezomCms\Requests\Services\Request1CService;
use WezomCms\ServicesOrders\Helpers\Price;
use WezomCms\Users\Http\Requests\Api\User\ChangeStatusFrom1CRequest;
use WezomCms\Users\Models\User;
use WezomCms\Users\Models\UserLoyalty;
use WezomCms\Users\Repositories\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function create(array $data): User
    {
        if(isset($data['userId'])){
            $model = $this->userRepository->byId($data['userId'],[], 'id', false);
        } else {
            $model = new User();
        }

        $model->first_name = $data['firstName'] ?? null;
        $model->last_name = $data['lastName'] ?? null;
        $model->email = $data['email'] ?? null;
        $model->patronymic = $data['patronymic'] ?? null;
        $model->phone = $data['phone'];
        $model->password = Hash::make(User::DEFAULT_PASSWORD);
        $model->fcm_token = $data['token'] ?? null;
        $model->device_id = $data['device_id'] ?? null;

        if(!$model->save()){
            throw new \Exception('Not created user');
        }

        return $model;
    }

    public function createLoyalty(User $user)
    {
        $loyalty = new UserLoyalty();
        $loyalty->user_id = $user->id;
        $loyalty->save();
    }

    /**
     * @param array $data
     * @param User $user
     * @return User
     * @throws \Exception
     */
    public function edit(array $data, User $user): User
    {
        $user->first_name = $data['firstName'];
        $user->last_name = $data['lastName'];
        $user->patronymic = $data['patronymic'] ?? null;
        $user->email = $data['email'] ?? null;

        if(!$user->save()){
            throw new \Exception('Not edit user');
        }

        return $user;
    }

    public function changePhone($phone, User $user, $comment): User
    {
        $user->phone = $phone;
        $user->change_phone_comment = $comment;
        $user->save();

        return $user;
    }

    /**
     * @param string $token
     * @param User $user
     * @return User
     * @throws \Exception
     */
    public function setFcmToken(string $token, User $user): User
    {
        $user->fcm_token = $token;

        if(!$user->save()){
            throw new \Exception('Not set fcm token');
        }

        return $user;
    }

    /**
     * @param $status
     * @param User $user
     * @return User
     * @throws \Exception
     */
    public function setStatus(User $user, $status): User
    {
        $user->status = $status;

        if(!$user->save()){
            throw new \Exception('Not set status');
        }

        return $user;
    }

    /**
     * @param $deviceId
     * @param User $user
     * @return User
     * @throws \Exception
     */
    public function setDeviceId(User $user, string $deviceId): User
    {
        $user->device_id = $deviceId;

        if(!$user->save()){
            throw new \Exception('Not set deviceId');
        }

        return $user;
    }

    /**
     * @param User $user
     * @param $loyaltyLevel
     * @param null $loyaltyType
     * @return User
     * @throws \Exception
     */
    public function setLoyalty(User $user, $loyaltyLevel, $loyaltyType, $levelUpAmount, $buyCars): User
    {
        $data = [
            'loyalty_type' => $loyaltyType,
            'loyalty_level' => $loyaltyLevel,
            'level_up_amount' => $levelUpAmount ? Price::toDB($levelUpAmount) : null,
            'buy_cars' => $buyCars,
        ];

        $user->loyalty()->updateOrCreate(['user_id' => $user->id], $data);


        return $user;
    }

    /**
     * @param User $user
     * @param ChangeStatusFrom1CRequest $request
     * @return User
     * @throws \Exception
     */
    public function changeStatusFrom1C(User $user, ChangeStatusFrom1CRequest $request): User
    {
        $user = $this->setStatus($user, $request['AccountStatusID']);
        if(isset($request['LoyaltyProgramTypeID'])){
            $user = $this->setLoyalty(
                $user,
                $request['LoyaltyLevelID'],
                $request['LoyaltyProgramTypeID'],
                $request['LevelUpAmount'],
                $request['PurchasedСars']
            );
        }

        return $user;
    }


}
