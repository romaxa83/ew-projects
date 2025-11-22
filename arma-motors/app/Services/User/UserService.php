<?php

namespace App\Services\User;

use App\DTO\User\UserDTO;
use App\DTO\User\UserEditDTO;
use App\Events\User\EditUser;
use App\Exceptions\ErrorsCode;
use App\Models\User\User;
use App\Repositories\User\UserRepository;
use App\Services\Localizations\LocalizationService;
use App\Services\Order\OrderService;
use App\Services\Sms\SmsVerifyService;
use App\Services\Tokenizer;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use App\ValueObjects\Token;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use DB;
use Illuminate\Support\Str;
use phpseclib3\Crypt\EC\Formats\Signature\ASN1;

class UserService
{
    public function __construct(
        protected LocalizationService $localizationService,
        protected SmsVerifyService $smsVerifyService,
        protected CarService $carService,
        protected OrderService $orderService,
        protected UserRepository $repo,
    )
    {}

    public function create(UserDTO $dto): User
    {
        DB::beginTransaction();
        try {

            // @todo обговорить, если с токен не валиден или стух будет кинуто исключение,
            // @todo и пользователь не сохраниться, стоит ли изменить поведение
            if($dto->hasActionToken()){
                // если есть токен и он валиден, верифицируем номер и удаляем запись
                $obj = $this->smsVerifyService->getAndCheckByActionToken($dto->getActionToken());
                $dto->phoneVerify();
                $obj->delete();
            }

            $model = new User();
            $model->name = $dto->getName();
            $model->phone = $dto->getPhone();
            $model->phone_verify = $dto->getPhoneVerify();
            $model->setPassword($dto->getPassword());
            $model->lang = $this->localizationService->getDefaultSlug();
            $model->egrpoy = $dto->getEgrpoy();
            $model->fcm_token = $dto->getFcmToken();
            $model->device_id = $dto->getDeviceId();
            $model->salt = Str::random(20);

            if($dto->getEmail()){
                $model->email = $dto->getEmail();
            }
            if($dto->getPhoneVerify()){
                $model->status = User::ACTIVE;
            }

            $model->save();

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function update(UserEditDTO $dto, User $model): User
    {
        DB::beginTransaction();
        try {
            $model->name = $dto->changeName() ? $dto->getName() : $model->name;
            $model->fcm_token = $dto->changeFcmToken() ? $dto->getFcmToken() : $model->fcm_token;
            $model->device_id = $dto->changeDeviceId() ? $dto->getDeviceId() : $model->device_id;
            $model->lang = $dto->changeLang() ? $dto->getLang() : $model->lang;

            if($dto->changeEgrpoy()){
                $model->egrpoy = $dto->changeEgrpoy() ? $dto->getEgrpoy() : $model->egrpoy;
                // кидаем изменения в систему аа
                if($model->uuid){
                    $model->save();
                    event(new EditUser($model));
                }
            }
            $model->save();

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function editFromAA(User $model, array $data): User
    {
        DB::beginTransaction();
        try {
            if(isset($data['status'])){
                User::assertStatus($data['status']);
            }

            $model->uuid = $data['uuid'] ?? $model->uuid;
            $model->status = $data['status'] ?? $model->status;
            $model->egrpoy = $data['codeOKPO'] ?? $model->egrpoy;
            $model->name = $data['name'] ?? $model->name;

            if(isset($data['verify']) && $data['verify']){
                $model->status = User::VERIFY;
            }

            if(isset($data['email'])){
                if($this->repo->existByEmail($data['email'], $model->id)){
                    throw new \InvalidArgumentException("Email already exist another user", ErrorsCode::BAD_REQUEST);
                }

                $model->email = new Email($data['email']);
                $model->email_verify = true;
            }

            if(null !== $model->new_phone && isset($data['newPhone'])){
                $newPhone = new Phone($data['newPhone']);
                if($newPhone->compare($model->new_phone)){
                    $model->phone = $newPhone;
                    $model->new_phone = null;
                    $model->phone_edit_at = CarbonImmutable::now();

                    // @todo кинуть пуш, что изменен телефон
                }
            }

            $model->save();

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function editEmail(User $model, null|Email $email): User
    {
        try {
            $model->email = $email;
            $model->email_verify = false;

            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function createNewPhoneRecord(User $model, Phone $phone, string $comment = null): User
    {
        try {
            $model->new_phone = $phone;
            $model->new_phone_comment = $comment;
            $model->phone_edit_at = Carbon::now();

            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function editPhone(User $model, Phone $phone, $comment): User
    {
        $model->phone = $phone;
        $model->new_phone_comment = $comment;
        $model->new_phone = null;

        $model->save();

        return $model;
    }

    public function removeNewPhone(User $model): User
    {
        try {
            $model->new_phone = null;
            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function changeHasNewNotifications(User $model, bool $hasNew): User
    {
        try {
            $model->has_new_notifications = $hasNew;
            $model->save();

            return $model;
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function changeStatus(User $model,int $status): User
    {
        try {
            User::assertStatus($status);

            $model->status = $status;
            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function changePassword(User $model, string $password): User
    {
        try {
            $model->setPassword($password);
            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function completeFromAA(User $model, null|array $data): User
    {
        logger('COMPLETE FROM AA', [
            'model' => $model,
            'data' => $data,
        ]);

        if(null !== $data){
            $model->uuid = $data['id'] ?? null;
            $model->status = User::VERIFY;
            // если у пользователя нет егрпоу, добаляем его
            // @todo продумать кейс если они не совпадают
            if(null == $model->egrpoy){
                $model->egrpoy = $data['codeOKPO'] ?? null;
            }

            $model->save();
        }

        return $model;
    }

    public function setUuid(User $model, string $uuid): User
    {
        $model->uuid = $uuid;
        $model->save();

        return $model;
    }

    public function setDeviceId(User $model, string $deviceId): User
    {
        $model->device_id = $deviceId;
        $model->save();

        return $model;
    }

    public function delete(User $model, bool $force = false): void
    {
        DB::beginTransaction();
        try {
            foreach ($model->orders()->withTrashed()->get() as $order){
                $this->orderService->delete($order, $force);
            }
            foreach ($model->cars()->withTrashed()->get() as $car){
                $this->carService->delete($car, $force);
            }

            if($force){
                $model->forceDelete();
            } else {
                $model->delete();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function restore(User $model): User
    {
        DB::beginTransaction();
        try {
            if(!$model->trashed()){
                throw new \Exception(__('error.model not trashed'));
            }

            $model->restore();
            $model->cars()->restore();
            // @todo восстановить заявки

            DB::commit();
            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function getEmailToken(): Token
    {
        return (new Tokenizer(new CarbonInterval(config('user.verify_email.email_token_expired'))))
            ->generate(CarbonImmutable::now());
    }
}
