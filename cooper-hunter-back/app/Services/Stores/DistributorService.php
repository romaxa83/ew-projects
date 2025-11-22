<?php

declare(strict_types=1);

namespace App\Services\Stores;

use App\Dto\Stores\Distributors\DistributorDto;
use App\Models\Locations\IpRange;
use App\Models\Stores\Distributor;
use App\ValueObjects\Point;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DistributorService
{
    public const ZIP_FILTER_REGEX = '/%s[ .,]*(usa|canada)?$/i';

    //200 miles ~ 322 kilometers
    public const RADIUS = 322;

    public function getList(array $args, array $with): Collection
    {
        $ds = $this->filterBySpecificZip(
            $args,
            $this->getDistributors($args, $with)
        );

        //if nothing found by filters, we should to display the closest distributor
        if (array_key_exists('query', $args)
            && array_key_exists(
                'coordinates',
                $args
            )
            && $ds->isEmpty()
        ) {
            return $this->resolveDistributorsForMap($args, $with);
        }

        return $ds;
    }

    public function filterBySpecificZip(array $args, Collection $distributors): Collection
    {
        if (isset($args['query']) && ($zip = $args['query']) && $distributors->count() > 1) {
            $dsByZip = $distributors->filter(
                static fn(Distributor $d) => (bool)preg_match(
                    sprintf(self::ZIP_FILTER_REGEX, $zip),
                    $d->address_metaphone
                )
            );

            if ($dsByZip->isNotEmpty()) {
                $distributors = $dsByZip;
            }
        }

        return $distributors;
    }

    public function getDistributors(array $args, array $with): Collection
    {
        return Distributor::query()
            ->filter($args)
            ->with($with)
            ->get();
    }

    protected function resolveDistributorsForMap(array $args, array $with): Collection
    {
        $distributorsByUserLocation = $this->findDistributorsForUserLocation($with);

        if ($distributorsByUserLocation->isNotEmpty()) {
            return $distributorsByUserLocation;
        }

        return $this->findDistributorsByDefaultLocationAndRadius($args, $with);
    }

    protected function findDistributorsForUserLocation(array $with = []): Collection
    {
        if (!$userIp = request()?->ip()) {
            return Collection::make([]);
        }

        $ip = ip2long($userIp);

        $range = IpRange::query()
            ->whereBetweenColumns(DB::raw($ip), ['ip_from', 'ip_to'])
            ->first();

        if (!$range) {
            return Collection::make([]);
        }

        $args = [
            'radius' => $range->coordinates->asCoordinatesWithRadius(100)
        ];

        return $this->getDistributors($args, $with);
    }

    protected function findDistributorsByDefaultLocationAndRadius(array $args, array $with): Collection
    {
        $args['radius'] = array_merge(
            $coordinates = $args['coordinates'],
            ['radius' => self::RADIUS]
        );

        unset($args['coordinates'], $args['query']);

        $ds = $this->getDistributors($args, $with);

        if ($ds->isNotEmpty()) {
            return $ds;
        }

        return Distributor::query()
            ->with($with)
            ->addDistance(
                Point::byCoordinates($coordinates),
                $distance = 'distance_in_km'
            )
            ->orderByDesc($distance)
            ->take(1)
            ->get();
    }

    public function create(DistributorDto $dto): Distributor
    {
        return $this->store(new Distributor(), $dto);
    }

    protected function store(
        Distributor $distributor,
        DistributorDto $dto
    ): Distributor {
        $this->fill($distributor, $dto);

        $distributor->save();

        $this->saveTranslations($distributor, $dto);

        return $distributor;
    }

    protected function fill(Distributor $distributor, DistributorDto $dto): void
    {
        $distributor->state_id = $dto->getStateId();
        $distributor->active = $dto->getActive();
        $distributor->coordinates = $dto->getCoordinates()->asPoint();
        $distributor->address = $dto->getAddress();
        $distributor->address_metaphone = $dto->getAddressSearchMetaphone();
        $distributor->link = $dto->getLink();
        $distributor->phone = $dto->getPhone();
    }

    protected function saveTranslations(
        Distributor $distributor,
        DistributorDto $dto
    ): void {
        foreach ($dto->getTranslations() as $translation) {
            $distributor->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'title' => $translation->getTitle(),
                ]
            );
        }
    }

    public function update(
        Distributor $distributor,
        DistributorDto $dto
    ): Distributor {
        return $this->store($distributor, $dto);
    }

    public function toggle(Distributor $distributor): Distributor
    {
        $distributor->active = !$distributor->active;
        $distributor->save();

        return $distributor;
    }

    public function delete(Distributor $distributor): bool
    {
        return $distributor->delete();
    }
}
