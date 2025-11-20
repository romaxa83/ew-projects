<?php

namespace App\DTO\Report;

class ClientDto
{
    public $clientID = null;

    public $customerID;
    public $firstName;
    public $lastName;
    public $companyName;
    public $phone;
    public $comment;

    public $type;
    public $mdID;
    public $quantityMachine;

    private $isJDClient = false;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        if(isset($args['client_id']) && !empty($args['client_id'])){
            $self->clientID = $args['client_id'];
            $self->isJDClient = true;
        }

        $self->customerID = $args['customer_id'] ?? null;
        $self->firstName = $args['customer_first_name'] ?? null;
        $self->lastName = $args['customer_last_name'] ?? null;
        $self->companyName = $args['company_name'] ?? null;
        $self->phone = $args['customer_phone'] ?? null;
        $self->comment = $args['comment'] ?? null;

        $self->type = $args['type'] ?? null;
        $self->mdID = $args['model_description_id'] ?? null;
        $self->quantityMachine = $args['quantity_machine'] ?? null;

        return $self;
    }

    public function isJDClient(): bool
    {
        return $this->isJDClient;
    }
}
