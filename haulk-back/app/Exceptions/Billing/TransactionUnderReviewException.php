<?php


namespace App\Exceptions\Billing;


use Exception;

class TransactionUnderReviewException extends Exception
{
    public const MESSAGE = 'Invoice payment is under review. Please contact support.';

    private string $transID;

    public function __construct(string $transID)
    {
        parent::__construct();

        $this->transID = $transID;
        $this->message = trans(self::MESSAGE);
    }

    /**
     * @return string
     */
    public function getTransID(): string
    {
        return $this->transID;
    }
}
