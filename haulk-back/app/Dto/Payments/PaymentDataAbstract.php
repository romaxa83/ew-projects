<?php


namespace App\Dto\Payments;


abstract class PaymentDataAbstract
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    abstract public function getId();
    abstract public function getData();
    abstract public function getCardNumber();
    abstract public function getCardDate();
    abstract public function getCardType();
    abstract public function getBillingName();
}
