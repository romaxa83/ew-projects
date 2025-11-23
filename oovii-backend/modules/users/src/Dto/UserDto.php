<?php

namespace WezomCms\Users\Dto;

class UserDto
{
    public $name;
    public $surname;
    public $patronymic;
    public $email;
    public $phone;
    public $password;
    public $lang;
    public $fcmToken;
    public $deviceId;
    public ?int $ref_id;

    private function __construct()
    {}

    public static function byRegistry(array $data): self
    {
        $self = new self();

        $self->name = $data['name'];
        $self->surname = $data['surname'];
        $self->email = $data['email'];
        $self->phone = prettyPhone($data['phone']);
        $self->password = $data['password'] ?? config("cms.users.users.password_default");
        $self->lang = $data['lang'] ?? config('cms.core.translations.app.default');
        $self->fcmToken = $data['fcmToken'] ?? null;
        $self->deviceId = $data['deviceId'] ?? null;
        $self->ref_id = data_get($data, 'ref_id');

        return $self;
    }

    public static function byLogin(array $data): self
    {
        $self = new self();

        $self->lang = $data['lang'] ?? null;
        $self->fcmToken = $data['fcmToken'] ?? null;
        $self->deviceId = $data['deviceId'] ?? null;

        return $self;
    }

    public static function byEdit(array $data): self
    {
        $self = new self();

        $self->name = $data['name'] ?? null;
        $self->surname = $data['surname'] ?? null;
        $self->patronymic = $data['patronymic'] ?? null;
        $self->email = $data['email'] ?? null;
        $self->lang = $data['lang'] ?? null;

        return $self;
    }

    public static function byChangePhone(array $data): self
    {
        $self = new self();

        $self->phone = prettyPhone($data['phone']);

        return $self;
    }
}
