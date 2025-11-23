<?php

namespace WezomCms\Users\Services;

use Exception;
use Hash;
use Illuminate\Http\Response;
use WezomCms\Orders\Dto\UserAddressDto;
use WezomCms\Orders\Models\UserAddress;
use WezomCms\Users\Dto\UserDto;
use WezomCms\Users\Models\User;

class UserService
{
    public function create(UserDto $dto): User
    {
        $model = new User();
        $model->name = $dto->name;
        $model->surname = $dto->surname;
        $model->email = $dto->email;
        $model->phone = $dto->phone;
        $model->phone_verified = true;
        $model->password = Hash::make($dto->password);
        $model->lang = $dto->lang;
        $model->fcm_token = $dto->fcmToken;
        $model->device_id = $dto->deviceId;
        $model->ref_id = $dto->ref_id;

        $model->save();

        return $model;
    }

    public function edit(User $model, UserDto $dto): User
    {
        $model->name = $dto->name ?? $model->name;
        $model->surname = $dto->surname ?? $model->surname;
        $model->patronymic = $dto->surname ?? $model->patronymic;
        $model->email = $dto->email ?? $model->email;
        $model->lang = $dto->lang ?? $model->lang;

        $model->save();

        return $model;
    }

    public function changePhone(User $model, UserDto $dto): User
    {
        $model->phone = $dto->phone;

        $model->save();

        return $model;
    }

    public function delete(User $model)
    {
        return $model->delete();
    }

    public function updateByLogin(User $model, UserDto $dto): User
    {
        $model->lang = $dto->lang ?? $model->lang;
        $model->fcm_token = $dto->fcmToken ?? $model->fcm_token;
        $model->device_id = $dto->deviceId ?? $model->device_id;

        $model->save();

        return $model;
    }

    public function addToWishlist(User $model, $productId, $collectionID = null): User
    {
        if(in_array($productId, $model->wishlist()->pluck('id')->toArray())){
            throw new Exception(
                __('cms-catalog::admin.products.exception.exist in wishlist', [
                    "id" => $productId
                ]),
                Response::HTTP_BAD_REQUEST
            );
        }

        $model->wishlist()->attach($productId, ['collection_id' => $collectionID]);

        return $model;
    }

    public function massAddToWishlist(User $model, array $productIds): User
    {
        $model->wishlist()->syncWithoutDetaching($productIds);

        return $model;
    }

    public function removeFromWishlist(User $model, $productId): User
    {
        $model->wishlist()->detach($productId);

        return $model;
    }

    public function clearWishlist(User $model): User
    {
        $model->wishlist()->detach();

        return $model;
    }

    public function createUserAddress(User $user, UserAddressDto $dto): UserAddress
    {
        $address = new UserAddress();
        $address->user_id = $user->id;
        $this->fillUserAddress($address, $dto);

        $address->save();

        return $address;
    }

    public function updateUserAddress(UserAddress $address, UserAddressDto $dto): UserAddress
    {
        $this->fillUserAddress($address, $dto);

        $address->save();

        return $address;
    }

    private function fillUserAddress(UserAddress $address, UserAddressDto $dto): UserAddress
    {
        $address->region_code = $dto->getRegionCode();
        $address->region = $dto->getRegionName();
        $address->city_code = $dto->getCityCode();
        $address->city = $dto->getCityName();
        $address->postal_code = $dto->getPostalCode();
        $address->name = $dto->getName();
        $address->address = $dto->getAddress();

        return $address;
    }
}
