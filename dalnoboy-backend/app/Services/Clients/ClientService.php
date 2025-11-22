<?php


namespace App\Services\Clients;


use App\Dto\Clients\ClientBanDto;
use App\Dto\Clients\ClientDto;
use App\Dto\PhoneDto;
use App\Exceptions\Clients\ClientUniqException;
use App\Models\Clients\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClientService
{
    public function create(ClientDto $dto): Client
    {
        return $this->editClient($dto, new Client());
    }

    public function update(ClientDto $dto, Client $client): Client
    {
        return $this->editClient($dto, $client);
    }

    private function editClient(ClientDto $dto, Client $client): Client
    {
        $client = $this->checkUniqueClient($dto, $client);

        $client->name = $dto->getName();
        $client->contact_person = $dto->getContactPerson();
        $client->edrpou = $dto->getEDRPOU();
        $client->inn = $dto->getINN();
        $client->manager_id = $dto->getManagerId();
        $client->active = $dto->isActive();
        $client->is_moderated = $dto->isModerated();

        if ($client->isDirty()) {
            $client->save();
        }

        $this->setPhones($dto->getPhones(), $client);

        return $client->refresh();
    }

    private function checkUniqueClient(ClientDto $dto, Client $client): Client
    {
        $similarClient = Client::query()
            ->where('id', '<>', $client->id)
            ->where('edrpou', $dto->getEDRPOU())
            ->where('inn', $dto->getINN())
            ->first();

        if (!$similarClient) {
            return $client;
        }

        if (!isBackOffice() && (!$similarClient->active || !$similarClient->isModerated())) {
            return $similarClient;
        }

        if (!$dto->isOffline()) {
            throw new ClientUniqException();
        }

        return $similarClient;
    }

    /**
     * @param PhoneDto[] $phones
     * @param Client $client
     */
    private function setPhones(array $phones, Client $client): void
    {
        $client
            ->phones()
            ->delete();

        $client->phones()
            ->createMany(
                array_map(
                    fn(PhoneDto $phoneDto) => [
                        'phone' => $phoneDto->getPhone(),
                        'is_default' => $phoneDto->isDefault()
                    ],
                    $phones
                )
            );
    }

    public function ban(ClientBanDto $dto, Client $client): Client
    {
        $client->ban_reason = $dto->getReason();
        $client->show_ban_in_inspection = $dto->getShowInInspection();
        $client->save();

        return $client;
    }

    public function delete(Client $client): bool
    {
        return $client->delete();
    }

    public function show(array $args, array $relation): LengthAwarePaginator
    {
        return Client::filter($args)
            ->with($relation)
            ->paginate(
                perPage: $args['per_page'],
                page: $args['page']
            );
    }
}
