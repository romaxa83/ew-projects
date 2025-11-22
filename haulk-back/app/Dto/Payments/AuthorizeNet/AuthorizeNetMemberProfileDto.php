<?php


namespace App\Dto\Payments\AuthorizeNet;


use App\Dto\Payments\PaymentDataAbstract;

class AuthorizeNetMemberProfileDto extends PaymentDataAbstract
{
    public function getData(): array
    {
        return $this->data;
    }

    public function getId()
    {
        return $this->data['customerProfileId'] ?? null;
    }

    public function getCardNumber()
    {
        $cardNumber = $this->data['creditCard']['cardNumber'] ?? null;

        if ($cardNumber) {
            $cardNumber = preg_replace('/[^0-9]/', '0', $cardNumber);
            $cardNumberArr = array_pad(
                str_split($cardNumber),
                -16,
                '0'
            );
            $cardNumber = implode('', $cardNumberArr);
        }

        return $cardNumber;
    }

    public function getCardDate()
    {
        $expirationDate = $this->data['creditCard']['expirationDate'] ?? null;

        if ($expirationDate) {
            $expirationDateArr = explode('-', $expirationDate);
            $expirationDate = $expirationDateArr[1] . '/' . substr($expirationDateArr[0], 2);
        }

        return $expirationDate;
    }

    public function getCardType()
    {
        return $this->data['creditCard']['cardType'] ?? null;
    }

    public function getProfileId()
    {
        return $this->data['customerProfileId'] ?? null;
    }

    public function getPaymentProfileId()
    {
        return $this->data['customerPaymentProfileId'] ?? null;
    }

    public function getBillingName(): ?string
    {
        return isset($this->data['billTo']['firstName'], $this->data['billTo']['lastName'])
            ? $this->data['billTo']['firstName'] . ' ' . $this->data['billTo']['lastName']
            : null;
    }
}
