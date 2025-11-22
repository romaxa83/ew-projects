<?php


namespace App\Dto\Payments;


class PaymentMethodRequestDto
{
    protected string $merchantCustomerId;
    protected string $customerEmail;
    protected string $customerFirstName;
    protected string $customerLastName;
    protected string $customerAddress;
    protected string $customerCity;
    protected string $customerState;
    protected string $customerZip;
    protected string $customerCountry;
    protected string $cardNumber;
    protected string $cardExpireMonth;
    protected string $cardExpireYear;
    protected string $cardCvv;

    public function __construct(
        string $merchantCustomerId,
        string $customerEmail,
        string $customerFirstName,
        string $customerLastName,
        string $customerAddress,
        string $customerCity,
        string $customerState,
        string $customerZip,
        string $customerCountry,
        string $cardNumber,
        string $cardExpireMonth,
        string $cardExpireYear,
        string $cardCvv
    )
    {
        $driver = config('billing.providers.driver');

        $this->merchantCustomerId = config('billing.providers.authorize_net.'.$driver.'.merchant_customer_id_prefix') . $merchantCustomerId;
        $this->customerEmail = $customerEmail;
        $this->customerFirstName = $customerFirstName;
        $this->customerLastName = $customerLastName;
        $this->customerAddress = $customerAddress;
        $this->customerCity = $customerCity;
        $this->customerState = $customerState;
        $this->customerZip = $customerZip;
        $this->customerCountry = $customerCountry;
        $this->cardNumber = $cardNumber;
        $this->cardExpireMonth = $cardExpireMonth;
        $this->cardExpireYear = $cardExpireYear;
        $this->cardCvv = $cardCvv;
    }

    public function getMerchantCustomerId(): string
    {
        return $this->merchantCustomerId;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getCardMonth(): string
    {
        return $this->cardExpireMonth;
    }

    public function getCardYear(): string
    {
        return $this->cardExpireYear;
    }

    public function getCardCvv(): string
    {
        return $this->cardCvv;
    }

    public function getCustomerFirstName(): string
    {
        return $this->customerFirstName;
    }

    public function getCustomerLastName(): string
    {
        return $this->customerLastName;
    }

    public function getCustomerAddress(): string
    {
        return $this->customerAddress;
    }

    public function getCustomerCity(): string
    {
        return $this->customerCity;
    }

    public function getCustomerState(): string
    {
        return $this->customerState;
    }

    public function getCustomerZip(): string
    {
        return $this->customerZip;
    }

    public function getCustomerCountry(): string
    {
        return $this->customerCountry;
    }

}
