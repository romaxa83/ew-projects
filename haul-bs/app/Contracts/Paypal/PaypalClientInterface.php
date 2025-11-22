<?php

namespace App\Contracts\Paypal;

use Illuminate\Http\Client\Response;

interface PaypalClientInterface
{
    public const API_BASE_URL = 'https://api-m.paypal.com/';
    public const API_SANDBOX_URL = 'https://api-m.sandbox.paypal.com/';

    public function sendRequest(string $uri, array $data = [], string $method = 'post', array $headers = []): Response;

    public function handleResponse(Response $response): Response;
}
