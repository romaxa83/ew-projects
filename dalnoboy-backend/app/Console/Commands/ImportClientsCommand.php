<?php

namespace App\Console\Commands;

use App\Enums\Utilities\MorphModelNameEnum;
use App\Imports\ClientsImport;
use App\Models\Clients\Client;
use App\Models\Managers\Manager;
use App\Rules\Clients\EDRPOURule;
use App\Rules\Clients\INNRule;
use App\Services\Admins\AdminService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportClientsCommand extends Command
{
    private const DEFAULT_CITY = 'Київ';
    private const DEFAULT_REGION_ID = 27;
    protected $signature = 'clients:import';
    protected $description = 'Импорт клиентов из двух xlsx файлов (clients_edrpou, clients_inn).';

    /**
     * @param AdminService $service
     */
    public function handle(AdminService $service): void
    {
        try {
            DB::beginTransaction();
            $collection = Excel::toCollection(new ClientsImport(), database_path('files/clients_edrpou.xlsx'))
                ->first()
                ->merge(
                    Excel::toCollection(new ClientsImport(), database_path('files/clients_inn.xlsx'))
                        ->first()
                )
                ->filter(
                    fn (Collection $row) => $row['nazvanie_klienta'] !== null
                )
                ->map(
                    function (Collection $row): Collection {
                        $row = $row->toArray();
                        $manager = $this->getManagerData($row);
                        return collect(
                            [
                                'name' => $row['nazvanie_klienta'],
                                'contact_person' => trim($row['kontaktnoe_lico_klienta'], " \t\n\r\0\x0B-_"),
                                'manager_id' => $manager['hash'],
                                'edrpou' => array_key_exists('edrpou', $row) ? preg_replace(
                                    "/[^0-9]+/",
                                    '',
                                    $row['edrpou']
                                ) : null,
                                'inn' => array_key_exists('inn', $row) ? preg_replace(
                                    "/[^0-9]+/",
                                    '',
                                    $row['inn']
                                ) : null,
                                'phone' => preg_replace("/[^0-9]+/", '', $row['telefon_kontaktnogo_lica']),
                                'manager' => $manager
                            ]
                        );
                    }
                )
                ->filter(
                    function (Collection $row): bool {
                        if ($row['manager_id'] === null) {
                            return false;
                        }
                        if (empty($row['edrpou']) && empty($row['inn'])) {
                            $this->warn(
                                "Client " . $row['name'] . ' will not be imported. Client does not have INN/EDRPOU'
                            );
                            return false;
                        }
                        if (!empty($row['inn']) && !empty($row['edrpou'])) {
                            $this->warn(
                                "Client " . $row['name'] . ' will not be imported. Client can have only INN or EDRPOU'
                            );
                            return false;
                        }
                        if (!empty($row['edrpou']) && !(new EDRPOURule())->passes('edrpou', $row['edrpou'])) {
                            $this->warn(
                                "Client " . $row['name'] . ' will not be imported. Client has incorrect EDRPOU'
                            );
                            return false;
                        }
                        if (!empty($row['inn']) && !(new INNRule())->passes('inn', $row['inn'])) {
                            $this->warn("Client " . $row['name'] . ' will not be imported. Client has incorrect INN');
                            return false;
                        }
                        return true;
                    }
                )
                ->toArray();
            $managersByHash = $this->saveManagers(array_column($collection, 'manager'));

            $whereIn = [
                'inn' => [],
                'edrpou' => []
            ];
            foreach ($collection as $item) {
                if ($item['inn']) {
                    $whereIn['inn'][] = $item['inn'];
                } else {
                    $whereIn['edrpou'][] = $item['edrpou'];
                }
            }
            $exists = $this->getExistsClients($whereIn);

            foreach ($collection as $item) {
                $key = $item['edrpou'] . '_' . $item['inn'];

                if ($exists->has($key)) {
                    continue;
                }

                $insert[$key] = "(" . "'" . preg_replace("/'/", "\'", $item['name']) . "'," . "'" . preg_replace(
                        "/'/",
                        "\'",
                        preg_quote($item['contact_person'])
                    ) . "'," . $managersByHash[$item['manager_id']] . "," . ($item['edrpou'] ? "'" . $item['edrpou'] . "'" : "NULL") . "," . ($item['inn'] ? "'" . $item['inn'] . "'" : "NULL") . "," . 1 . "," . 1 . "," . "'" . Carbon::now(
                    )
                        ->toDateTimeString() . "'," . "'" . Carbon::now()
                        ->toDateTimeString() . "'" . ")";
                $phones[$key] = $item['phone'];
            }
            if (!empty($insert) && !empty($phones)) {
                DB::insert(
                    "INSERT INTO clients(name, contact_person, manager_id, edrpou, inn, active, is_moderated, created_at, updated_at) values " . implode(
                        ",",
                        $insert
                    )
                );

                $this->info("Clients were saved");
                unset($insert);
                $exists = $this->getExistsClients($whereIn)
                    ->toArray();
                foreach ($phones as $key => $phone) {
                    $insert[] = "(" . "'" . MorphModelNameEnum::client(
                        )->key . "'," . $exists[$key] . "," . "'" . mb_substr($phone, 0, 20) . "'," . "1" . ")";
                }
                if (!empty($insert)) {
                    DB::insert(
                        "INSERT INTO phones(owner_type, owner_id, phone, is_default) values " . implode(",", $insert)
                    );

                    $this->info("Clients' phones were saved");
                    unset($insert);
                }
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function getManagerData(array $row): ?array
    {
        $result = [
            'first_name' => '',
            'last_name' => '',
            'second_name' => '',
            'hash' => null,
            'city' => self::DEFAULT_CITY,
            'region_id' => self::DEFAULT_REGION_ID,
            'phone' => preg_replace("/[^0-9]+/", '', $row['telefon_menedzera'])
        ];
        if (empty($result['phone'])) {
            $this->warn(
                "Client " . $row['nazvanie_klienta'] . ' will not be imported. Manager does not have phone number'
            );
            return $result;
        }
        try {
            $fio = explode(" ", trim($row['fio_menedzera'], " \t\n\r\0\x0B-_"));
            $secondName = !empty($fio[2]) ? trim($fio[2]) : null;
            $result['first_name'] = trim($fio[0], " \t\n\r\0\x0B,");
            $result['last_name'] = trim($fio[1]);
            $result['second_name'] = !empty($secondName) ? $secondName : null;
        } catch (Throwable) {
            $this->warn("Client " . $row['nazvanie_klienta'] . ' will not be imported. Manager has problem with FIO');
            return $result;
        }
        $result['hash'] = md5($result['first_name'] . $result['last_name'] . $result['second_name']);
        return $result;
    }

    private function saveManagers(array $managers): array
    {
        $managersByKey = [];
        foreach ($managers as $manager) {
            $managersByKey[$manager['hash']] = $manager;
        }
        unset($manager);
        $hash = array_keys($managersByKey);
        $exists = Manager::query()
            ->select(['hash'])
            ->whereIn('hash', $hash)
            ->get()
            ->pluck('hash');
        foreach ($managersByKey as $manager) {
            if ($exists->contains($manager['hash'])) {
                continue;
            }
            $insert[] = "(" . "'" . $manager['hash'] . "','" . preg_replace(
                    "/'/",
                    "\'",
                    $manager['first_name']
                ) . "','" . preg_replace(
                    "/'/",
                    "\'",
                    $manager['last_name']
                ) . "'," . ($manager['second_name'] !== null ? "'" . preg_replace(
                        "/'/",
                        "\'",
                        $manager['second_name']
                    ) . "'" : "NULL") . "," . $manager['region_id'] . "," . "'" . $manager['city'] . "'," . "'" . Carbon::now(
                )
                    ->toDateTimeString() . "'," . "'" . Carbon::now()
                    ->toDateTimeString() . "'" . ")";
            $phones[] = [
                'hash' => $manager['hash'],
                'phone' => $manager['phone']
            ];
        }
        if (!empty($insert)) {
            DB::insert(
                "INSERT INTO managers(hash, first_name, last_name, second_name, region_id, city, created_at, updated_at) values " . implode(
                    ",",
                    $insert
                )
            );

            $this->info("Managers were saved");
            unset($insert);
        }
        $exists = Manager::query()
            ->select(['id', 'hash'])
            ->whereIn('hash', $hash)
            ->get()
            ->pluck('id', 'hash')
            ->toArray();

        if (empty($phones)) {
            return $exists;
        }
        foreach ($phones as $phone) {
            $insert[] = "(" . "'" . MorphModelNameEnum::manager(
                )->key . "'," . $exists[$phone['hash']] . "," . "'" . mb_substr(
                    $phone['phone'],
                    0,
                    20
                ) . "'," . "1" . ")";
        }
        if (!empty($insert)) {
            DB::insert("INSERT INTO phones(owner_type, owner_id, phone, is_default) values " . implode(",", $insert));

            $this->info("Managers' phones were saved");
            unset($insert);
        }
        return $exists;
    }

    private function getExistsClients(array $whereIn): Collection
    {
        $query = Client::query()
            ->select(['id', 'edrpou', 'inn']);
        if (!empty($whereIn['inn'])) {
            $query->orWhereIn('inn', $whereIn['inn']);
        }
        if (!empty($whereIn['edrpou'])) {
            $query->orWhereIn('edrpou', $whereIn['edrpou']);
        }
        return $query->get()
            ->mapWithKeys(
                fn (Client $client) => [$client->edrpou . '_' . $client->inn => $client->id]
            );
    }
}
