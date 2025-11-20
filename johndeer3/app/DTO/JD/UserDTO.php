<?php

namespace App\DTO\JD;

class UserDTO
{
    public $jdID;
    public $login;
    public $email;
    public $mobileNumber;
    public $phone;
    public $status;
    public $countryID;
    public $dealerID;
    public $dealerIDs = [];
    public $reallyDealerID;
    public $lang;
    public $firstName;
    public $lastName;
    public $password = null;
    public $role = null;
    public $egIDs = [];

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->jdID = $args['id'] ?? null;
        $self->login = $args["login"];
        $self->email = $args["email"];
        $self->mobileNumber = $args["mobile_number"];
        $self->status = $args['status'] == 1 ? true : false;
        $self->countryID = $args["country_id"] ?? null;
        $self->dealerID = $args["dealer_id"] ?? null;
        $self->reallyDealerID = $args["really_dealer_id"] ?? null;
        $self->lang = $args["lang"] ?? null;
        $self->firstName = $args["first_name"];
        $self->lastName = $args["last_name"];
        $self->dealerIDs = $args["dealer_ids"] ?? [];

        return $self;
    }

    public static function byRequest(array $data): self
    {
        $self = new self();

        $self->login = $data["login"];
        $self->email = $data["email"];
        $self->mobileNumber = $data["phone"];
        $self->countryID = $data["country_id"] ?? null;
        $self->dealerID = $data["dealer_id"] ?? null;
        $self->lang = $data["lang"] ?? null;
        $self->firstName = $data["first_name"];
        $self->lastName = $data["last_name"];
        $self->dealerIDs = $data["dealer_ids"] ?? [];
        $self->egIDs = $data["eg_ids"] ?? [];
        $self->role = $data["role"] ?? null;

        return $self;
    }
}


