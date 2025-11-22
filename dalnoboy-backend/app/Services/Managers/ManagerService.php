<?php


namespace App\Services\Managers;


use App\Dto\Managers\ManagerDto;
use App\Dto\PhoneDto;
use App\Exceptions\Managers\ManagerHasClientsException;
use App\Exceptions\Managers\ManagerUniqException;
use App\Models\Managers\Manager;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ManagerService
{
    public function create(ManagerDto $dto): Manager
    {
        return $this->editManager($dto, new Manager());
    }

    private function editManager(ManagerDto $dto, Manager $manager): Manager
    {
        $this->checkUniqueManager($dto, $manager);

        $manager->first_name = $dto->getFirstName();
        $manager->last_name = $dto->getLastName();
        $manager->second_name = $dto->getSecondName();
        $manager->hash = $dto->getHash();
        $manager->city = $dto->getCity();
        $manager->region_id = $dto->getRegionId();

        if ($manager->isDirty()) {
            $manager->save();
        }

        $this->setPhones($dto->getPhones(), $manager);

        return $manager->refresh();
    }

    private function checkUniqueManager(ManagerDto $dto, Manager $manager): void
    {
        if (Manager::query()
            ->where('id', '<>', $manager->id)
            ->where('hash', $dto->getHash())
            ->exists()
        ) {
            throw new ManagerUniqException();
        }
    }

    /**
     * @param PhoneDto[] $phones
     * @param Manager $manager
     */
    private function setPhones(array $phones, Manager $manager): void
    {
        $manager
            ->phones()
            ->delete();

        $manager->phones()
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

    public function update(ManagerDto $dto, Manager $manager): Manager
    {
        return $this->editManager($dto, $manager);
    }

    public function delete(Manager $manager): bool
    {
        if ($manager->clients()
            ->exists()) {
            throw new ManagerHasClientsException();
        }
        return $manager->delete();
    }

    public function show(array $args): LengthAwarePaginator
    {
        return Manager::filter($args)
            ->paginate(
                perPage: $args['per_page'],
                page: $args['page']
            );
    }
}
