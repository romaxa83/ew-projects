<?php

namespace App\DTO\User;

use App\Traits\AssetData;

class UserEditDTO
{
    use AssetData;

    private null|string $name;
    private null|string $egrpoy;
    private null|string $deviceId;
    private null|string $fcmToken;
    private null|string $lang;
    // for edit
    private bool $changeName;
    private bool $changeEgrpoy;
    private bool $changeDeviceId;
    private bool $changeFcmToken;
    private bool $changeLang;

    private function __construct(array $data)
    {
        $this->setChangeName(static::checkFieldExist($data, 'name'));
        $this->setChangeLang(static::checkFieldExist($data, 'lang'));
        $this->setChangeDeviceId(static::checkFieldExist($data, 'deviceId'));
        $this->setChangeFcmToken(static::checkFieldExist($data, 'fcmToken'));
        $this->setChangeEgrpoy(static::checkFieldExist($data, 'egrpoy'));
    }

    public static function byArgs(array $args): self
    {
        self::assetHasValue($args, 'name');
        self::assetHasValue($args, 'lang');

        $self = new self($args);

        $self->name = self::getPrettyValue($args, 'name');
        $self->egrpoy = self::getPrettyValue($args, 'egrpoy');
        $self->deviceId = self::getPrettyValue($args, 'deviceId');
        $self->fcmToken = self::getPrettyValue($args, 'fcmToken');
        $self->lang = self::getPrettyValue($args, 'lang');

        return $self;
    }

    public function getName(): null|string
    {
        return $this->name;
    }

    public function getDeviceId(): string|null
    {
        return $this->deviceId;
    }

    public function getEgrpoy(): string|null
    {
        return $this->egrpoy;
    }

    public function getFcmToken(): string|null
    {
        return $this->fcmToken;
    }

    public function getLang(): string|null
    {
        return $this->lang;
    }

    // присутствовали поля при редактировании
    public function changeName(): bool
    {
        return $this->changeName;
    }
    public function changeEgrpoy(): bool
    {
        return $this->changeEgrpoy;
    }
    public function changeLang(): bool
    {
        return $this->changeLang;
    }
    public function changeDeviceId(): bool
    {
        return $this->changeDeviceId;
    }
    public function changeFcmToken(): bool
    {
        return $this->changeFcmToken;
    }

    private function setChangeName(bool $bool): void
    {
        $this->changeName = $bool;
    }
    public function setChangeEgrpoy(bool $bool): void
    {
        $this->changeEgrpoy = $bool;
    }
    public function setChangeLang(bool $bool): void
    {
        $this->changeLang = $bool;
    }
    public function setChangeDeviceId(bool $bool): void
    {
        $this->changeDeviceId = $bool;
    }
    public function setChangeFcmToken(bool $bool): void
    {
        $this->changeFcmToken = $bool;
    }
}


