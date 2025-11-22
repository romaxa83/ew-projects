<?php

namespace Tests\Unit\GraphQL;

use Core\WebSocket\Connections\SubscriptionEntity;
use Tests\TestCase;

class ParserTest extends TestCase
{
    /**
     * @dataProvider subscriptionsDataProvider
     */
    public function test_parse_subscription_args(
        string $subscription,
        string $expectSubscriptionName,
        ?string $expectChannelName
    ): void {
        preg_match(SubscriptionEntity::PARSE_PATTERN, $subscription, $matches);

        $subscription = $matches['subscription'] ?? null;
        $channel = $matches['channel'] ?? null;

        self::assertEquals($expectSubscriptionName, $subscription);
        self::assertEquals($expectChannelName, $channel);
    }

    public function subscriptionsDataProvider(): array
    {
        return [
            'data1' => [
                'subscription {
                    subName (channel: "ch.1") {
                        id
                        event
                    }
                }',
                'subName',
                'ch.1'
            ],
            'data2' => [
                'subscription {
                    subName2 {
                        id
                        event
                    }
                }',
                'subName2',
                null
            ],
        ];
    }
}
