<?php


namespace App\Services\Permissions\Payments;


use App\Dto\Payments\AuthorizeNet\AuthorizeNetMemberProfileDto;
use App\Dto\Payments\PaymentDataAbstract;
use App\Dto\Payments\PaymentMethodRequestDto;
use App\Exceptions\Billing\TransactionUnderReviewException;
use Exception;
use Illuminate\Support\Facades\Http;

class AuthorizeNetPaymentService implements PaymentProviderInterface
{
    private const AUTHORIZE_NET_PROVIDER_NAME = 'authorize_net';
    private const AUTHORIZE_NET_RESULT_OK = 'Ok';
    private const AUTHORIZE_NET_RESULT_ERROR = 'Error';
    public const AUTHORIZE_NET_CVV_SUCCESS = 900;
    public const AUTHORIZE_NET_CVV_FAIL = 901;
    private const AUTHORIZE_NET_RESPONSE_CODE_APPROVED = 1;
    private const AUTHORIZE_NET_RESPONSE_CODE_REVIEW = 4;

    private string $driver;

    public function __construct()
    {
        $this->driver = config('billing.providers.driver');
    }

    public function getProviderName(): string
    {
        return self::AUTHORIZE_NET_PROVIDER_NAME;
    }

    private function appendMerchantAuthentication(array $requestData): array
    {
        $data['merchantAuthentication'] = [
            'name' => config('billing.providers.authorize_net.'.$this->driver.'.name'),
            'transactionKey' => config('billing.providers.authorize_net.'.$this->driver.'.transactionKey'),
        ];

        foreach ($requestData as $k => $v) {
            $data[$k] = $v;
        }

        return $data;
    }

    private function sendRequest(array $requestData)
    {
        $response = Http::post(
            config('billing.providers.authorize_net.'.$this->driver.'.api_url'),
            $requestData
        );

        $responseFixed = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response->body());

        return json_decode($responseFixed, true, 512, JSON_THROW_ON_ERROR);
    }

    private function isResponseOk(?array $response): bool
    {
        return $response
            && is_array($response)
            && isset($response['messages']['resultCode'])
            && $response['messages']['resultCode'] === self::AUTHORIZE_NET_RESULT_OK;
    }

    private function isResponseError(?array $response): bool
    {
        return $response
            && is_array($response)
            && isset($response['messages']['resultCode'])
            && $response['messages']['resultCode'] === self::AUTHORIZE_NET_RESULT_ERROR;
    }

    public function storePaymentData(PaymentMethodRequestDto $dto): AuthorizeNetMemberProfileDto
    {
        try {

            $response = $this->sendRequest(
                [
                    'createCustomerProfileRequest' => $this->appendMerchantAuthentication(
                        [
                            "profile" => [
                                "merchantCustomerId" => $dto->getMerchantCustomerId(),
                                "email" => $dto->getCustomerEmail(),
                                "paymentProfiles" => [
                                    "billTo" => [
                                        "firstName" => $dto->getCustomerFirstName(),
                                        "lastName" => $dto->getCustomerLastName(),
                                        "address" => $dto->getCustomerAddress(),
                                        "city" => $dto->getCustomerCity(),
                                        "state" => $dto->getCustomerState(),
                                        "zip" => $dto->getCustomerZip(),
                                        "country" => $dto->getCustomerCountry(),
                                    ],
                                    "payment" => [
                                        "creditCard" => [
                                            "cardNumber" => $dto->getCardNumber(),
                                            "expirationDate" => $dto->getCardYear() . '-' . $dto->getCardMonth(),
                                            "cardCode" => $dto->getCardCvv()
                                        ]
                                    ]
                                ]
                            ],
                            "validationMode" => "liveMode",
                        ]
                    )
                ]
            );

            if ($this->isResponseOk($response)) {
                $customerProfileId = $response['customerProfileId'];
                $customerPaymentProfileId = $response['customerPaymentProfileIdList'][0];

                $response = $this->sendRequest(
                    [
                        'getCustomerPaymentProfileRequest' => $this->appendMerchantAuthentication(
                            [
                                "customerProfileId" => $customerProfileId,
                                "customerPaymentProfileId" => $customerPaymentProfileId,
                                "unmaskExpirationDate" => true
                            ]
                        )
                    ]
                );

                if($this->isResponseOk($response)) {
                    return new AuthorizeNetMemberProfileDto(
                        [
                            'customerProfileId' => $customerProfileId,
                            'customerPaymentProfileId' => $customerPaymentProfileId,
                            'creditCard' => $response['paymentProfile']['payment']['creditCard'],
                            'billTo' => $response['paymentProfile']['billTo'],
                        ]
                    );
                }
            }

            if($this->isResponseError($response)) {
                throw new Exception($response['messages']['message'][0]['text']);
            }

            throw new Exception(trans('Error saving payment data.'));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /*public function getPaymentData(string $id): ?AuthorizeNetMemberProfileDto
    {
        try {
            $response = $this->sendRequest(
                [
                    'getCustomerProfileRequest' => $this->appendMerchantAuthentication(
                        [
                            "customerProfileId" => $id
                        ]
                    )
                ]
            );

            if ($this->isResponseOk($response)) {
                return $response['profile']['paymentProfiles'][0]['payment']['creditCard'];
            }
        } catch (Exception $e) {}

        return null;
    }*/

    public function deleteByStoredPaymentData(PaymentDataAbstract $paymentData): bool
    {
        try {
            $response = $this->sendRequest(
                [
                    'deleteCustomerProfileRequest' => $this->appendMerchantAuthentication(
                        [
                            "customerProfileId" => $paymentData->getProfileId()
                        ]
                    )
                ]
            );

            return $this->isResponseOk($response);
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteByUserData(...$userData): bool
    {
        if (!isset($userData[0], $userData[1])) {
            return false;
        }

        try {
            $paymentData = $this->getPaymentProfileByUserCredentials($userData[0], $userData[1]);

            if ($paymentData) {
                return $this->deleteByStoredPaymentData($paymentData);
            }
        } catch (Exception $e) {}

        return false;
    }

    /**
     * @throws Exception
     */
    public function makePayment(PaymentDataAbstract $paymentData, float $amount): string
    {
        $response = $this->sendRequest(
            [
                'createTransactionRequest' => $this->appendMerchantAuthentication(
                    [
                        "transactionRequest" => [
                            "transactionType" => "authCaptureTransaction",
                            "amount" => "$amount",
                            "currencyCode" => config('billing.providers.authorize_net.'.$this->driver.'.currency_code_usd'),
                            "profile" => [
                                "customerProfileId" => $paymentData->getProfileId(),
                                "paymentProfile" => [
                                    "paymentProfileId" => $paymentData->getPaymentProfileId()
                                ]
                            ],
                            /*"processingOptions" => [
                                "isSubsequentAuth" => "true"
                            ],
                            "subsequentAuthInformation" => [
                                "originalNetworkTransId" => "123456789123456",
                                "originalAuthAmount" => "45.00",
                                "reason" => "resubmission"
                            ],*/
                            "authorizationIndicatorType" => [
                                "authorizationIndicator" => "final"
                            ]
                        ]
                    ]
                )
            ]
        );

        if (
            $this->isResponseOk($response)
            && isset($response['transactionResponse']['responseCode'], $response['transactionResponse']['transId'])
            && (int)$response['transactionResponse']['responseCode'] === self::AUTHORIZE_NET_RESPONSE_CODE_APPROVED
        ) {
            return $response['transactionResponse']['transId'];
        }

        if (
            $this->isResponseOk($response)
            && isset($response['transactionResponse']['responseCode'], $response['transactionResponse']['transId'])
            && (int)$response['transactionResponse']['responseCode'] === self::AUTHORIZE_NET_RESPONSE_CODE_REVIEW
        ) {
            throw new TransactionUnderReviewException($response['transactionResponse']['transId']);
        }

        if (isset($response['transactionResponse']['errors'][0]['errorText'])) {
            throw new Exception($response['transactionResponse']['errors'][0]['errorText']);
        }
        if (isset($response['transactionResponse']['messages'][0]['description'])) {
            throw new Exception($response['transactionResponse']['messages'][0]['description']);
        }

        if (isset($response['messages']['message'][0]['text'])) {
            throw new Exception($response['messages']['message'][0]['text']);
        }


        throw new Exception(trans('Payment error.'));
    }

    /**
     * @throws Exception
     */
    public function getPaymentProfileByUserCredentials($userID, $customerEmail): ?AuthorizeNetMemberProfileDto
    {
        try {
            $response = $this->sendRequest(
                [
                    'getCustomerProfileRequest' => $this->appendMerchantAuthentication(
                        [
                            "merchantCustomerId" => $userID,
                            "email" => $customerEmail,
                            "unmaskExpirationDate" => true
                        ]
                    )
                ]
            );

            if (
                $this->isResponseOk($response)
                && isset($response['profile']['paymentProfiles'][0])
            ) {
                $customerProfileId = $response['profile']['customerProfileId'];
                $paymentProfile = $response['profile']['paymentProfiles'][0];

                return new AuthorizeNetMemberProfileDto(
                    [
                        'customerProfileId' => $customerProfileId,
                        'customerPaymentProfileId' => $paymentProfile['customerPaymentProfileId'],
                        'creditCard' => $paymentProfile['payment']['creditCard'],
                        'billTo' => $paymentProfile['billTo'],
                    ]
                );
            }

            if($this->isResponseError($response)) {
                throw new Exception($response['messages']['message'][0]['text']);
            }

            return null;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getTransactionStatus(string $transID): ?int
    {
        try {
            $response = $this->sendRequest(
                [
                    'getTransactionDetailsRequest' => $this->appendMerchantAuthentication(
                        [
                            "transId" => $transID
                        ]
                    )
                ]
            );

            if (
                $this->isResponseOk($response)
                && isset($response['transaction']['responseCode'])
                && (int)$response['transaction']['responseCode'] !== self::AUTHORIZE_NET_RESPONSE_CODE_REVIEW
            ) {
                return (
                    (int)$response['transaction']['responseCode'] === self::AUTHORIZE_NET_RESPONSE_CODE_APPROVED
                        ? self::TRANSACTION_APPROVED
                        : self::TRANSACTION_DECLINED
                );
            }
        } catch (Exception $e) {}

        return null;
    }
}
