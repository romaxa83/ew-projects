<?php


namespace App\Services\Drivers;


use App\Dto\Drivers\DriverDto;
use App\Dto\PhoneDto;
use App\Exceptions\Drivers\DriverUniqException;
use App\Models\Drivers\Driver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DriverService
{
    public function create(DriverDto $dto): Driver
    {
        return $this->editDriver($dto, new Driver());
    }

    private function editDriver(DriverDto $dto, Driver $driver): Driver
    {
        $driver = $this->checkUniqueDriver($dto, $driver);

        $driver->first_name = $dto->getFirstName();
        $driver->last_name = $dto->getLastName();
        $driver->second_name = $dto->getSecondName();
        $driver->email = $dto->getEmail();
        $driver->client_id = $dto->getClientId();
        $driver->comment = $dto->getComment();
        $driver->active = $dto->isActive();
        $driver->is_moderated = $dto->isModerated();

        if ($driver->isDirty()) {
            $driver->save();
        }

        $this->setPhones($dto->getPhones(), $driver);

        return $driver->refresh();
    }

    private function checkUniqueDriver(DriverDto $dto, Driver $driver): Driver
    {
        $similar = Driver::query()
            ->where('id', '<>', $driver->id)
            ->where(
                function (Builder $builder) use ($dto)
                {
                    $builder
                        ->orWhere(
                            fn(Builder $orBuilder) => $orBuilder
                                ->where('first_name', $dto->getFirstName())
                                ->where('last_name', $dto->getLastName())
                                ->where('second_name', $dto->getSecondName())
                        );
                    if ($dto->getEmail()) {
                        $builder->orWhere(
                            'email',
                            $dto->getEmail()
                        );
                    }
                }
            )
            ->first();

        if (!$similar) {
            return $driver;
        }

        if ($similar->active || (!$similar->active && isBackOffice())) {
            throw new DriverUniqException();
        }
        return $similar;
    }

    /**
     * @param PhoneDto[] $phones
     * @param Driver $driver
     */
    private function setPhones(array $phones, Driver $driver): void
    {
        $driver
            ->phones()
            ->delete();

        $driver->phones()
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

    public function update(DriverDto $dto, Driver $driver): Driver
    {
        return $this->editDriver($dto, $driver);
    }

    public function delete(Driver $driver): bool
    {
        return $driver->delete();
    }

    public function show(array $args, array $relation): LengthAwarePaginator
    {
        return Driver::filter($args)
            ->with($relation)
            ->paginate(
                perPage: $args['per_page'],
                page: $args['page']
            );
    }
}
