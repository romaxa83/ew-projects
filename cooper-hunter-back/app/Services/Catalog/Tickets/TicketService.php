<?php

namespace App\Services\Catalog\Tickets;

use App\Dto\Catalog\Tickets\TicketByTechnicianDto;
use App\Dto\Catalog\Tickets\TicketDto;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Technicians\Technician;
use App\Services\OneC\Client;
use Core\Exceptions\TranslatedException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TicketService
{
    public function __construct(private Client $client)
    {
    }

    public function updateByTechnician(Ticket $ticket, TicketByTechnicianDto $dto): Ticket
    {
        $this->assertTechnicianCanUpdateTicket($ticket);

        $this->saveTranslations($ticket, $dto);

        return $ticket;
    }

    private function assertTechnicianCanUpdateTicket(Ticket $ticket): void
    {
        if ($ticket->status->updatable()) {
            return;
        }

        throw new TranslatedException(__('Ticket could not be updated by technician'));
    }

    protected function saveTranslations(Ticket $model, TicketDto|TicketByTechnicianDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $model->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'title' => $translation->getTitle() ?: '',
                    'description' => $translation->getDescription() ?: '',
                ]
            );
        }
    }

    public function createByTechnician(Technician $technician, TicketByTechnicianDto $dto): Ticket
    {
        $ticket = $this->create($dto->getTicketDto());

        $this->syncOrderParts($dto, $ticket);

        $ticket->comment = $dto->getComment();

        if (($entity = $this->client->createTicket($ticket, $technician)) && $entity->success) {
            if (Ticket::query()->where('code', $entity->code)->doesntExist()) {
                $ticket->code = $entity->code;
            }

            $ticket->guid = $entity->guid;

            $ticket->status = $entity->status ?: $ticket->status;
        }

        $ticket->save();

        return $ticket;
    }

    public function create(TicketDto $dto): Ticket
    {
        return $this->store(new Ticket(), $dto);
    }

    protected function store(Ticket $model, TicketDto $dto): Ticket
    {
        $this->fill($dto, $model);

        $model->save();

        if (count($parts = $dto->getOrderPartsIds()) > 0) {
            $model->orderPartsRelation()->sync($parts);
        }

        $this->saveTranslations($model, $dto);

        return $model;
    }

    protected function fill(TicketDto $dto, Ticket $model): void
    {
        $model->serial_number = $dto->getSerialNumber();
        $model->guid = $dto->getGuid();
        $model->code = $dto->getCode();
        $model->status = $dto->getStatus();
        $model->order_parts = $dto->getOrderParts();
        $model->case_id = $dto->getCaseID();
    }

    public function syncOrderParts(TicketByTechnicianDto $dto, Ticket $ticket): void
    {
        if (!$dto->hasOrderParts()) {
            return;
        }

        $ids = [];

        foreach ($dto->getOrderParts() as $part) {
            $ids[] = $part->getId();
        }

        $parts = OrderCategory::query()->whereKey($ids)
            ->with('translation')
            ->get();

        $ticket->order_parts = $parts->pluck('translation')
            ->pluck('title')
            ->toArray();

        $ticket->orderPartsRelation()->sync($dto->getSyncingParts());
    }

    public function update(Ticket $model, TicketDto $dto): Ticket
    {
        return $this->store($model, $dto);
    }

    public function delete(Ticket $model): bool
    {
        return $model->delete();
    }

    public function getList(array $args): LengthAwarePaginator
    {
        return Ticket::filter($args)
            ->orderByDesc('id')
            ->paginate(
                perPage: $args['per_page'],
                page: $args['page']
            );
    }
}
