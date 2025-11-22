<?php

namespace App\Services\DeliveryServices\Handlers;

use App\Dto\Delivery\DeliveryAddressRateDto;
use Illuminate\Http\Client\Response;

interface DeliveryDriverRateHandler
{
    public function handle(DeliveryAddressRateDto $dto): ?array;
    public function validate(DeliveryAddressRateDto $dto): bool;
    public function mapToStructure(Response $response): array;
    public function execute(DeliveryAddressRateDto $dto): Response;
}
