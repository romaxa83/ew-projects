<?php

namespace App\Dto\Payments;

use Illuminate\Http\Request;

class PayPalWebhookSignatureDto
{
    private const HTTP_AUTH_ALGO_HEADER = 'paypal-auth-algo';
    private const HTTP_CERT_URL_HEADER = 'paypal-cert-url';
    private const HTTP_TRANSMISSION_TIME_HEADER = 'paypal-transmission-time';
    private const HTTP_TRANSMISSION_ID_HEADER = 'paypal-transmission-id';
    private const HTTP_TRANSMISSION_SIG_HEADER = 'paypal-transmission-sig';

    private ?string $certUrl;

    private ?string $transmissionId;

    private ?string $transmissionTime;

    private ?string $transmissionSig;

    private ?string $authAlgo;

    private ?string $body;

    public static function byRequest(Request $request): self
    {
        $dto = new self();

        $dto->certUrl = $request->header(self::HTTP_CERT_URL_HEADER);
        $dto->transmissionId = $request->header(self::HTTP_TRANSMISSION_ID_HEADER);
        $dto->transmissionTime = $request->header(self::HTTP_TRANSMISSION_TIME_HEADER);
        $dto->transmissionSig = $request->header(self::HTTP_TRANSMISSION_SIG_HEADER);
        $dto->authAlgo = $request->header(self::HTTP_AUTH_ALGO_HEADER);

        $dto->body = $request->getContent();

        return $dto;
    }

    public function getCertUrl(): ?string
    {
        return $this->certUrl;
    }

    public function getTransmissionId(): ?string
    {
        return $this->transmissionId;
    }

    public function getTransmissionTime(): ?string
    {
        return $this->transmissionTime;
    }

    public function getTransmissionSig(): ?string
    {
        return $this->transmissionSig;
    }

    public function getAuthAlgo(): ?string
    {
        return $this->authAlgo;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function isExistsAllData(): bool
    {
        return $this->certUrl && $this->transmissionId && $this->transmissionTime && $this->transmissionSig && $this->authAlgo && $this->body;
    }
}
