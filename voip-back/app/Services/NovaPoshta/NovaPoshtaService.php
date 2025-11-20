<?php

namespace App\Services\NovaPoshta;

use App\Models\Settlements\Settlement;
use App\ValueObjects\Point;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;
use JsonException;

class NovaPoshtaService
{
    #[NoReturn] public function syncRecordFromApiWithDB(array $dataFromApi): void
    {
        $settlement = Settlement::query()
            ->updateOrCreate(
                [
                    'ref' => $dataFromApi['Ref'],
                    'type' => $dataFromApi['SettlementType'],
                    'region' => $dataFromApi['Region'],
                    'area' => $dataFromApi['Area'],
                ],
                [
                    'index1' => $dataFromApi['Index1'],
                    'index2' => $dataFromApi['Index2'],
                    'coatsu1' => $dataFromApi['IndexCOATSU1'],
                    'coordinates' => new Point(
                        (float)$dataFromApi['Longitude'],
                        (float)$dataFromApi['Latitude']
                    ),
                ]
            );

        foreach (languages() as $language) {
            if ($language->slug === 'ru') {
                continue;
            }

            if ($language->slug === 'uk') {
                $settlement->translates()
                    ->updateOrCreate(
                        [
                            'language' => $language->slug,
                        ],
                        [
                            'description' => $dataFromApi['Description'],
                            'type_description' => $dataFromApi['SettlementTypeDescription'],
                            'region_description' => $dataFromApi['RegionsDescription'],
                            'area_description' => $dataFromApi['AreaDescription'],
                        ]
                    );
            }

            if ($language->slug === 'en') {
                $settlement->translates()
                    ->updateOrCreate(
                        [
                            'language' => $language->slug,
                        ],
                        [
                            'description' => $dataFromApi['DescriptionTranslit'],
                            'type_description' => $this->translateSettlementTypeDescription(
                                $dataFromApi['SettlementTypeDescriptionTranslit']
                            ),
                            'region_description' => $dataFromApi['RegionsDescriptionTranslit'],
                            'area_description' => $dataFromApi['AreaDescriptionTranslit'],
                        ]
                    );
            }
        }
    }

    protected function translateSettlementTypeDescription(string $typeDescription): string
    {
        return str_replace(
            array_keys(config('nova-poshta.translations')),
            array_values(config('nova-poshta.translations')),
            $typeDescription
        );
    }

    #[NoReturn] public function fetchSettlements(int $page = 1, int $limit = 150): array
    {
        $response = Http::post(
            $this->getApiUrl(),
            [
                'apiKey' => $this->getApiKey(),
                'modelName' => 'Address',
                'calledMethod' => 'getSettlements',
                'methodProperties' => [
                    'Page' => $page,
                    'Limit' => $limit,
                ],
            ]
        );

        try {
            $data = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);

            if (isset($data['success']) && $data['success']) {
                return $data;
            }

            Log::error('NovaPoshta API call. Warnings:', $data['warnings']);
            return [];
        } catch (JsonException $e) {
            Log::error('NovaPoshta API call. Message: ' . $e->getMessage());
            return [];
        }
    }

    protected function getApiUrl(): string
    {
        return config('nova-poshta.api-url');
    }

    protected function getApiKey(): ?string
    {
        return config('nova-poshta.api-key');
    }
}
