<?php

namespace App\Services\OneC;

use App\Entities\OneC\Tickets\TicketEntity;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Technicians\Technician;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

class Client
{
    public const TICKET_CREATE = 'hs/tickets/add';

    public function createTicket(Ticket $ticket, Technician $technician): TicketEntity|false
    {
        try {
            $orderParts = $ticket->orderPartsRelation;

            $data = [
                'serial' => $ticket->serial_number,
                'model' => $ticket->product->title,
                'contact' => [
                    'firstName' => $technician->first_name,
                    'lastName' => $technician->last_name,
                    'emailAddress' => $technician->getEmailString(),
                    'phoneNumber' => $technician->getPhoneString(),
                    'client_type' => $technician::ONEC_TYPE,
                ],
                'order_parts' => $orderParts->map(
                    static fn(OrderCategory $c): array => [
                        'guid' => $c->guid,
                        'description' => $c->pivot?->description,
                    ]
                )->toArray(),
                'issue' => $ticket->translation->title,
                'solution' => $ticket->translation->description,
                'comment' => $ticket->comment ?? null,
            ];

            $response = $this->getConnection()
                ->post(self::TICKET_CREATE, $data);

            if ($response->successful()) {
                return TicketEntity::createFromResponse(
                    $response->body()
                );
            }

            logger($response->body());

            return false;
        } catch (Throwable $e) {
            logger($e);

            return false;
        }
    }

    protected function getConnection(): PendingRequest
    {
        return Http::withOptions(
            [
                'timeout' => config('onec.timeout'),
                'connect_timeout' => config('onec.connection_timeout'),

            ]
        )
            ->acceptJson()
            ->asJson()
            ->withBasicAuth(
                config('onec.login'),
                config('onec.password')
            )
            ->baseUrl($this->getBaseUrl());
    }

    protected function getBaseUrl(): string
    {
        $baseUrl = config('onec.base_url');

        if ($suffix = config('onec.base_url_suffix')) {
            $baseUrl = trim($baseUrl, '/') . '/' . trim($suffix, '/');
        }

        return trim($baseUrl, '/');
    }
}