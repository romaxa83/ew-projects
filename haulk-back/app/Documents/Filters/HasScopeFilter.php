<?php

namespace App\Documents\Filters;

/**
 * @mixin DocumentFilter
 */
trait HasScopeFilter
{
    public function carrierId(?int $carrierId): void
    {
        if (is_int($carrierId)) {
            $this->addBoolQuery(
                self::MUST,
                [
                    'term' => [
                        'carrier_id' => $carrierId,
                    ]
                ]
            );
            return;
        }
        $this->addBoolQuery(
            self::MUST_NOT,
            [
                'exists' => [
                    'field' => 'carrier_id'
                ]
            ]
        );
    }

    public function brokerId(?int $brokerId): void
    {
        if (is_int($brokerId)) {
            $this->addBoolQuery(
                self::MUST,
                [
                    'term' => [
                        'broker_id' => $brokerId,
                    ]
                ]
            );
            return;
        }
        $this->addBoolQuery(
            self::MUST_NOT,
            [
                'exists' => [
                    'field' => 'broker_id'
                ]
            ]
        );
    }
}
