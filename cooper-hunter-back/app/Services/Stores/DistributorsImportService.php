<?php

namespace App\Services\Stores;

use App\Models\Locations\State;
use App\Models\Stores\Distributor;
use App\ValueObjects\Phone;
use App\ValueObjects\Point;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class DistributorsImportService
{
    /**
     * @var State[]|Collection
     */
    protected array|Collection $states;

    public function seed(): void
    {
        $this->states = State::all()->keyBy('short_name');

        $file = database_path('files/csv/Website_locator.csv');

        $data = fastexcel()->import(
            $file,
            static fn(array $row) => array_map('trim', $row)
        );

        foreach ($data as $d) {
            if ($this->shouldSkip($d)) {
                continue;
            }

            $this->storeDistributor($d);
        }
    }

    /**
     * @param $d
     *
     * @return bool
     */
    public function shouldSkip($d): bool
    {
        return $d['Phone'] === 'online'
            || (
                empty($d['Latitude'])
                || empty($d['Longitude'])
            );
    }

    protected function storeDistributor(array $d): ?Distributor
    {
        $distributor = new Distributor();

        $state = $this->states->get($d['Region/State']);

        $distributor->state()->associate($state);
        $distributor->active = true;

        $distributor->coordinates = new Point(
            $this->extractFloat($d['Longitude']),
            $this->extractFloat($d['Latitude'])
        );

        $distributor->address = $this->makeAddress($d);
        $distributor->address_metaphone = makeSearchSlug($distributor->address);
        $distributor->link = $d['Online store/website'] ?: null;
        $distributor->phone = $this->getPhone($d['Phone']);

        $distributor->save();

        foreach (languages() as $language) {
            $distributor->translations()->updateOrCreate(
                [
                    'language' => $language->slug,
                ],
                [
                    'title' => $d['Distributor'],
                ]
            );
        }

        return $distributor;
    }

    protected function extractFloat(string $float): float
    {
        $float = str_replace(',', '.', $float);

        return (float)$float;
    }

    protected function makeAddress(array $d): string
    {
        return sprintf(
            '%s, %s, %s %s',
            $d['Street Address'],
            $d['City'],
            $d['Region/State'],
            $d['Zip Code'],
        );
    }

    protected function getPhone(string $phone): ?Phone
    {
        try {
            return new Phone($phone);
        } catch (Throwable) {
            return null;
        }
    }
}
