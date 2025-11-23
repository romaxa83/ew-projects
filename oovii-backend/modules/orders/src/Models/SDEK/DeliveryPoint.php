<?php

namespace WezomCms\Orders\Models\SDEK;

use AntistressStore\CdekSDK2\Entity\Responses\DeliveryPointsResponse;
use JetBrains\PhpStorm\Pure;

class DeliveryPoint
{
    public function __construct(private DeliveryPointsResponse $sdekDeliveryPoint)
    {

    }

    #[Pure]
    public function getCode(): string
    {
        return $this->sdekDeliveryPoint->getCode();
    }

    #[Pure]
    public function getFullName(): string
    {
        return sprintf(
            "%s, (%s)",
            $this->sdekDeliveryPoint->getName(),
            $this->sdekDeliveryPoint->getLocation()->getAddress()
        );
    }
}
