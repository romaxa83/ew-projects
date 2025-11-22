<?php

namespace App\Dto\Fueling;

use App\Models\Fueling\Fueling;
use Illuminate\Support\Arr;

class FuelingImportDto
{
    private ?string $card;
    private string $uuid;
    private string $invoice;
    private ?string $transaction_date;
    private ?string $user;
    private ?string $location;
    private ?string $state;
    private ?string $fees;
    private ?string $item;
    private ?string $unit_price;
    private ?string $quantity;
    private ?string $amount;

    public static function build(array $args): self
    {
        $self = new self();

        $self->card = (string) Arr::get($args, 'card');
        $date = Arr::get($args, 'tran_date');
        $self->transaction_date = $date;
        $self->user = Arr::get($args, 'driver_name');
        $self->invoice = Arr::get($args, 'invoice');
        $self->location = Arr::get($args, 'location_name');
        $self->state = Arr::get($args, 'state_prov');
        $self->fees = (string) Arr::get($args, 'fees');
        $self->item = (string) Arr::get($args, 'item');
        $self->unit_price = (string) Arr::get($args, 'unit_price');
        $self->quantity = (string) Arr::get($args, 'qty');
        $self->amount = (string) Arr::get($args, 'amt');

        return $self;
    }

    public function getUid(): string
    {
        $id = '';
        foreach (Fueling::UID_FORMATION_FIELDS as $field) {
            $id .= $this->$field;
        }
        return md5($id);
    }

    public function getCard(): ?string
    {
        return $this->card;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }
    public function getTransactionDate(): ?string
    {
        return $this->transaction_date;
    }

    public function getFullBody(): array
    {
        return [
            'uuid' => $this->getUid(),
            'card' => $this->getCard(),
            'invoice' => $this->invoice,
            'transaction_date' => $this->transaction_date,
            'user' => $this->user,
            'location' => $this->location,
            'state' => $this->state,
            'fees' => $this->fees,
            'item' => $this->item,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
        ];
    }
}
